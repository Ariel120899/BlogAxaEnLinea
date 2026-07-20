import { crearSesionInicial } from './sesiones-v2.js';
import { LP_CONFIG } from './lp-config.js';
import { MSIxBancos } from './msi-bancos.js';
import { obtenerTokenMAG, cargarSelectMarcas } from './catalogos-mag.js';

localStorage.clear();

const getToken = async () => {
    try {
        await obtenerTokenMAG();
    } catch (error) {
        console.error(error);
    }
};

const getTokenWS = async () => {
    try {
        const response = await fetch("https://wsservicios.gmag.com.mx/System/WsController/GenerarToken", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                API_USUARIO: {
                    USUARIO: "ADMIN",
                    CONTRASENIA: "Hola123"
                }
            }),
            redirect: "follow"
        });
        const result = await response.json();
        if (result?.Token) {
            localStorage.setItem("tokenWS", result.Token);
        }
    } catch (error) {
        console.error(error);
    }
};

getTokenWS();

const initForm = () => {
    localStorage.setItem("Ingreso", "False");
    localStorage.setItem("Motor", "NORMAL");

    getToken();

    const slcMarcas = document.getElementById("slc-marcas");
    if (!slcMarcas) return;

    slcMarcas.focus();
    cargarSelectMarcas(slcMarcas, { placeholder: "Selecciona una Marca", focus: true })
        .catch((e) => console.error(e));

    localStorage.setItem("marca", slcMarcas.value);
    localStorage.setItem("vecesConsultaAnio", 0);

    slcMarcas.addEventListener("change", function () {
        const marca = slcMarcas.value;
        if (marca === localStorage.getItem("marca")) return;

        fetch("https://apis.segurointeligente.mx/api/Catalogos/GetModelos", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Authorization: "Bearer " + localStorage.getItem("tokenMAG")
            },
            body: JSON.stringify({ nombreMarca: marca, rango: "2005" }),
            redirect: "follow"
        })
            .then((response) => response.json())
            .then((result) => {
                let options = "<option value='' selected='true' disabled='disabled'>Modelo</option>";
                (result?.response?.anio || []).forEach((a) => {
                    options += `<option value="${a}">${a}</option>`;
                });

                const slcAnio = document.getElementById("slc-anio");
                slcAnio.innerHTML = options;
                document.getElementById("slc-descripcion").innerHTML =
                    "<option value='' selected disabled>Submarca</option>";
                document.getElementById("slc-descripcionCompleta").innerHTML =
                    "<option value='' selected disabled>Selecciona la versión</option>";

                if (window.innerWidth > 880) slcAnio.focus();
            })
            .catch((error) => console.error("Error:", error));
    });

    document.getElementById("slc-anio").addEventListener("change", function () {
        const marca = slcMarcas.value;
        const anio = document.getElementById("slc-anio").value;

        fetch("https://apis.segurointeligente.mx/api/Catalogos/GetLineaSubMarca", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Authorization: "Bearer " + localStorage.getItem("tokenMAG")
            },
            body: JSON.stringify({ anio, nombreMarca: marca }),
            redirect: "follow"
        })
            .then((response) => response.json())
            .then((result) => {
                let options = "<option value='' selected='true' disabled='disabled'>Submarca</option>";
                (result?.response?.subMarca || []).forEach((submarca) => {
                    options += `<option value="${submarca}">${submarca}</option>`;
                });

                const slcDescripcion = document.getElementById("slc-descripcion");
                slcDescripcion.innerHTML = options;
                document.getElementById("slc-descripcionCompleta").innerHTML =
                    "<option value='' selected disabled>Selecciona la versión</option>";

                if (window.innerWidth > 880) slcDescripcion.focus();
            })
            .catch((error) => console.error("Error:", error));
    });

    document.getElementById("slc-descripcion").addEventListener("change", function () {
        const marca = slcMarcas.value;
        const anio = document.getElementById("slc-anio").value;
        const descripcion = document.getElementById("slc-descripcion").value;

        fetch(
            `https://apis.segurointeligente.mx/api/Catalogos/GetCevic?Marca=${encodeURIComponent(marca)}&Modelo=${encodeURIComponent(anio)}&Des=${encodeURIComponent(descripcion)}`,
            {
                method: "GET",
                headers: { Authorization: "Bearer " + localStorage.getItem("tokenMAG") },
                redirect: "follow"
            }
        )
            .then((response) => response.json())
            .then((result) => {
                if (result.message !== "Ok") return;

                const aseguradoraCia = (result.response || []).find((item) => item.aseguradora === "AXA");
                if (aseguradoraCia) {
                    let options = "<option value='' selected disabled>Selecciona la versión</option>";
                    (aseguradoraCia.descipciones || []).forEach((desc) => {
                        options += `<option value="${desc.cevic}">${desc.descripcion}</option>`;
                    });
                    document.getElementById("slc-descripcionCompleta").innerHTML = options;
                } else {
                    document.getElementById("slc-descripcionCompleta").innerHTML =
                        "<option value='Especial' selected>Especial</option>";
                }
            })
            .catch((error) => console.error("Error:", error));
    });
};

initForm();

const validarCodigoPostal = (cp) => /^\d{5}$/.test(String(cp || "").trim());

const validarNumeroCelular = (numero) => {
    if (!/^\d{10}$/.test(numero)) return false;
    if (/(\d)\1{3,}/.test(numero)) return false;
    return true;
};

let requestCount = 0;

const validatePhoneNumber = async (numero) => {
    if (!validarNumeroCelular(numero)) {
        return false;
    }

    if (requestCount >= 2) {
        return true;
    }

    try {
        const response = await fetch("https://wsgenerico.segurointeligente.mx/Servicios/ValidatePhone", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Authorization: "Bearer 68AS4D68A1D6SAD08AD9A1D8ASD9AD6A1BOWINBI"
            },
            body: JSON.stringify({ phone: "+52" + numero }),
            redirect: "follow"
        });
        const result = await response.json();
        requestCount++;
        return Boolean(result?.Valid);
    } catch (error) {
        console.error("Error:", error);
        // Si el servicio remoto falla, no bloquear si ya pasó validación local.
        return true;
    }
};

document.getElementById("formCotizacionGNP").addEventListener("submit", function (e) {
    e.preventDefault();

    const data = new FormData(e.target);
    const obj = {};
    data.forEach((value, key) => {
        obj[key] = value;
    });

    const faltantes = [];
    if (!obj.marca) faltantes.push("marca");
    if (!obj.modelo) faltantes.push("modelo");
    if (!obj.descripcion) faltantes.push("submarca");
    if (!obj.descripcionCompleta) faltantes.push("versión");
    if (!obj.FNacimiento) faltantes.push("edad");
    if (obj.genero === undefined || obj.genero === "") faltantes.push("género");
    if (!validarCodigoPostal(obj.CP)) faltantes.push("código postal (5 dígitos)");
    if (!obj.nombre) faltantes.push("nombre");
    if (!obj.ApPaterno) faltantes.push("apellido");
    if (!obj.correo) faltantes.push("correo");
    if (!obj.Tel) faltantes.push("celular");

    if (faltantes.length) {
        alert("Revisa estos campos: " + faltantes.join(", "));
        return;
    }

    const btn = document.getElementById("btnCG");
    btn.disabled = true;

    validatePhoneNumber(obj.Tel)
        .then(async (result) => {
            if (result !== true) {
                alert("El teléfono ingresado no es válido. Debe tener 10 dígitos y no repetir el mismo número 4 o más veces.");
                btn.disabled = false;
                return;
            }

            if (obj.descripcionCompleta === "Especial") {
                Loader(true);
                Generador(obj, false);
                return;
            }

            try {
                Loader(true);
                localStorage.setItem("DataBase", JSON.stringify(obj));
                await crearSesionInicial(obj, LP_CONFIG);

                const cotizacion = await Cotizacion(obj);
                const primaTotal = cotizacion?.response?.cotizacionInfo?.[0]?.primaTotal;

                if (cotizacion?.message === "Ok" && primaTotal != null && primaTotal !== "") {
                    localStorage.setItem("Cotizacion", JSON.stringify(cotizacion.response));
                    window.location.href = "comparativo.html";
                } else {
                    Loader(false);
                    Generador(obj, true);
                }
            } catch (error) {
                console.error("Error:", error);
                Loader(false);
                btn.disabled = false;
                alert(error?.message || "No se pudo iniciar la cotización. Verifica tus datos e intenta de nuevo.");
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            btn.disabled = false;
        });
});

const Generador = (Data) => {
    const firstPage = LP_CONFIG.firstPage || window.location.href.split("?")[0];

    fetch("https://wsservicios.gmag.com.mx/ZoohoTools/CRM/CrearProspectosSI", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Authorization: "Bearer " + localStorage.getItem("tokenWS")
        },
        body: JSON.stringify({
            ProspectoZoho: {
                email: Data.correo,
                mkT_Campaigns: Data.utmc && Data.utmc !== "N/A" ? Data.utmc : "",
                ramo: "AUTOMOVILES",
                zip_Code: Data.CP,
                firstPage,
                description: `El Vehiculo cotizado por el cliente, no se encuentra en catalogo de la aseguradora, Marca: ${Data.marca} Modelo: ${Data.modelo} Submarca: ${Data.descripcion} CP: ${Data.CP} Genero: ${Data.genero === "0" ? "Masculino" : "Femenino"} Fecha de Nacimiento: ${Data.FNacimiento} `,
                first_Name: Data.nombre,
                full_Name: `${Data.nombre} ${Data.ApPaterno}`,
                phone: "+52" + Data.Tel,
                genero: Data.genero,
                mobile: "+52" + Data.Tel,
                Last_Name: Data.ApPaterno,
                lead_Source: LP_CONFIG.leadsource || "LP-AUTO-AXASEGUROSENLINEA-SEM",
                aseguradora_Campana: LP_CONFIG.aseguradoracampana || "AXA",
                Fecha_de_Nacimiento: Data.FNacimiento,
                Marca: Data.marca,
                Modelo: Data.modelo,
                GCLID: Data.gclid || ""
            }
        }),
        redirect: "follow"
    })
        .then((response) => response.json())
        .then(() => {
            alert("Su vehículo requiere atención especializada");
            window.location.reload();
        })
        .catch((error) => console.error(error));
};

const Cotizacion = async (Data) => {
    const response = await fetch("https://apis.segurointeligente.mx/api/Cotizacion/GetCotizacionAseg", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Authorization: "Bearer " + localStorage.getItem("tokenMAG")
        },
        body: JSON.stringify({
            marca: Data.marca,
            modelo: Data.modelo,
            subMarca: Data.descripcion,
            cPostal: Data.CP,
            idGrupo: "35",
            emailVendedor: "e-commerce@segurointeligente.mx",
            formaPago: "CONTADO",
            fechaNacimiento: Data.FNacimiento + "T00:00:00.00Z",
            cobertura: "AMPLIA",
            genero: Data.genero,
            rfc: "",
            idcia: LP_CONFIG.idCIA || 7,
            cevic: Data.descripcionCompleta
        }),
        redirect: "follow"
    });

    return response.json();
};

export async function Loader(valor) {
    return new Promise((resolve) => {
        if (!valor) {
            const overlay = document.querySelector(".overlayM");
            if (overlay) overlay.style.display = "none";
            resolve();
            return;
        }

        const OVERLAY = document.createElement("div");
        const LOADER = document.createElement("span");
        const TEXT = document.createElement("span");
        const CONTAINER = document.createElement("div");

        OVERLAY.classList.add("overlayM");
        LOADER.classList.add("loaderM");
        TEXT.classList.add("text");
        CONTAINER.classList.add("loaderM-container");
        TEXT.textContent = "Preparando...";
        TEXT.id = "TextSpin";

        document.body.appendChild(OVERLAY);
        OVERLAY.appendChild(CONTAINER);
        CONTAINER.appendChild(LOADER);
        CONTAINER.appendChild(TEXT);

        const mensajes = [
            "Buscando las mejores coberturas AXA",
            "Aplicando descuentos",
            "Aplicando Meses sin Intereses",
            "Preparando cotización",
            "Personalizando cotización"
        ];

        let index = 0;
        const cambiarTexto = () => {
            TEXT.classList.remove("visible");
            setTimeout(() => {
                TEXT.textContent = mensajes[index];
                TEXT.classList.add("visible");
                index = (index + 1) % mensajes.length;
            }, 1000);
        };

        setInterval(cambiarTexto, 3000);
        TEXT.classList.add("visible");
        resolve();
    });
}

function fechapermitida() {
    let stringtabla = '<option value="" selected disabled>Selecciona tu edad</option>';

    for (let index = 18; index <= 80; index++) {
        const today = new Date();
        let dd = today.getDate();
        let mm = today.getMonth() + 1;
        const yyyy = today.getFullYear() - index;
        if (dd < 10) dd = "0" + dd;
        if (mm < 10) mm = "0" + mm;
        const value = `${yyyy}-${mm}-${dd}`;
        stringtabla += `<option value="${value}">${index}</option>`;
    }

    const fnaci = document.getElementById("Fnaci");
    if (fnaci) fnaci.innerHTML = stringtabla;
}

fechapermitida();

document.querySelectorAll(".soloNumeros").forEach(function (input) {
    input.addEventListener("input", function () {
        this.value = this.value.replace(/[^0-9]/g, "");
    });
});

document.querySelectorAll(".soloLetras").forEach(function (input) {
    input.addEventListener("input", function () {
        this.value = this.value.replace(/[^A-Za-zñÑáéíóúÁÉÍÓÚüÜ\s]/g, "");
    });
});

document.querySelectorAll("input[data-type='email']").forEach(function (input) {
    input.addEventListener("blur", function () {
        const re = /([A-Z0-9a-z_-][^@])+?@[^$#<>?]+?\.[\w]{2,4}/.test(this.value);
        if (!re) {
            this.style.borderColor = "#c00";
        } else {
            this.style.borderColor = "";
        }
    });
});

fetch("https://api.ipify.org/?format=jsonP", { method: "GET", redirect: "follow" })
    .then((response) => response.text())
    .then((result) => {
        const match = String(result).match(/\b\d{1,3}(?:\.\d{1,3}){3}\b/);
        if (match) localStorage.setItem("IPSesion", match[0]);
    })
    .catch((error) => console.log("error", error));

MSIxBancos(7).then((data) => {
    localStorage.setItem("Bancos", JSON.stringify(data));

    let maxMSI = null;
    const bancosConMSI = [];

    if (data != null) {
        data.response.forEach((banco) => {
            const msiValue = banco.MSI;
            if (msiValue && msiValue !== "SN MSI") {
                const msiNumbers = msiValue
                    .split(",")
                    .map((val) => parseInt(val.trim(), 10))
                    .filter((val) => !isNaN(val));

                if (msiNumbers.length > 0) {
                    maxMSI = maxMSI === null ? Math.min(...msiNumbers) : Math.min(maxMSI, ...msiNumbers);
                }
                bancosConMSI.push(banco.banco.nombre);
            }
        });

        localStorage.setItem("MSIDisponibles", maxMSI !== null ? maxMSI : "No disponible");
    }
});
