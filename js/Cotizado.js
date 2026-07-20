import { VamonosalMotor, Loader } from './Motor.js';
import {
    actualizarSesionDesdeCotizacion,
    actualizarSesionSeleccionada,
    obtenerHashSesion
} from './sesiones-v2.js';
import { LP_CONFIG } from './lp-config.js';
import { MSIxBancos } from './msi-bancos.js';
import { obtenerTokenMAG } from './catalogos-mag.js';

document.addEventListener("DOMContentLoaded", () => {
    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.innerHTML = value;
    };

    obtenerTokenMAG()
        .then(() => {
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
                                maxMSI =
                                    maxMSI === null
                                        ? Math.min(...msiNumbers)
                                        : Math.min(maxMSI, ...msiNumbers);
                            }
                            bancosConMSI.push(banco.banco.nombre);
                        }
                    });

                    const availableMSI = maxMSI !== null ? maxMSI : "No disponible";
                    localStorage.setItem("MSIDisponibles", availableMSI);

                    if (bancosConMSI.length > 0) {
                        setText(
                            "BancosParticipantesMSI",
                            "Bancos participantes a MSI: <br>" + bancosConMSI.join(", ")
                        );
                    }
                    setText("MSIDisponibles", `${availableMSI} MSI`);
                }
            });

            GetCoberturas(6)
                .then((data) => {
                    let coberturasHTML = "";

                    if (data.response) {
                        for (const key in data.response) {
                            if (!Object.prototype.hasOwnProperty.call(data.response, key)) continue;
                            const value = data.response[key];
                            if (value === "-" || value === null) continue;

                            let label = key;
                            switch (key) {
                                case "daños_Materiales":
                                    label = "Daños materiales";
                                    break;
                                case "robo_Total":
                                    label = "Robo total";
                                    break;
                                case "rc":
                                    label = "Responsabilidad civil";
                                    break;
                                case "rC_Catastrofica":
                                    label = "RC Catastrófica";
                                    break;
                                case "extension_RC":
                                    label = "Extensión RC";
                                    break;
                                case "gmo":
                                    label = "Gastos Médicos Ocupantes";
                                    break;
                                case "defensa_Legal":
                                    label = "Asistencia legal";
                                    break;
                                case "asistencia_Vial":
                                    label = "Asistencia vial";
                                    break;
                                case "accidentes_Automovilisticos_Conuctor":
                                    label = "Muerte accidental";
                                    break;
                            }

                            coberturasHTML += `
                                <div class="flexul">
                                    <img src="img/check-rojo.svg" alt="check" width="12">
                                    <div>
                                        <p>${label}:<br><strong>${value}</strong></p>
                                    </div>
                                </div>
                                <hr>
                            `;
                        }
                    }

                    setText("filasCoberturas", coberturasHTML);
                })
                .catch((error) => console.error("Error al obtener las coberturas:", error));
        })
        .catch((err) => console.error("Error al obtener token:", err));

    Loader(false);

    if (localStorage.getItem("leadidcpy") != null) {
        window.location.href = "index.html";
        return;
    }

    if (!obtenerHashSesion() || localStorage.getItem("Cotizacion") == null) {
        window.location.href = "index.html";
        return;
    }

    const DataBase = JSON.parse(localStorage.getItem("DataBase"));
    const CotizacionP = JSON.parse(localStorage.getItem("Cotizacion"));
    const Cotizacion = CotizacionP.cotizacionInfo[0];

    setText("nombredes", DataBase.nombre);
    setText("preciodes", formatCurrency(Cotizacion.primaTotal));
    setText("marcades", CotizacionP.marca);
    setText("modelodes", CotizacionP.subMarca);
    setText("aniodes", CotizacionP.modelo);
    setText("versiondes", Cotizacion.descripcionCompleta);
    setText("PrecioRegular", "Precio regular " + formatCurrency(Cotizacion.primaTotal * 1.16));

    actualizarSesionDesdeCotizacion(LP_CONFIG, Cotizacion, CotizacionP).catch((error) =>
        console.error("No se pudo actualizar la sesion al cargar la cotizacion:", error)
    );

    const redirectToThanks = () => {
        actualizarSesionSeleccionada(LP_CONFIG, {
            ID_COT_MAG: Cotizacion.id,
            PRIMA_NETA: Cotizacion.primaNeta,
            PRIMA_TOTAL: Cotizacion.primaTotal,
            ID_CIA: CotizacionP.idCIA || LP_CONFIG.idCIA,
            ASEGURADORA_CAMPANA: Cotizacion.nombreCIA || LP_CONFIG.aseguradoracampana,
            CVIC: Cotizacion.cevic,
            estado: 2
        })
            .then(() => {
                Loader(false);
                window.location.href = "thanks.html";
            })
            .catch((error) => {
                console.error(error);
                Loader(false);
            });
    };

    setTimeout(redirectToThanks, 300000);

    const btnMotor = document.getElementById("CompraOnline");
    const btnTelefono = document.getElementById("Tofono");
    const btnMotorM = document.getElementById("CompraOnlineM");
    const btnTelefonoM = document.getElementById("TofonoM");
    const Descarga = document.getElementById("link");

    const procesarCompraTelefono = () => {
        actualizarSesionSeleccionada(LP_CONFIG, {
            ID_COT_MAG: Cotizacion.id,
            PRIMA_NETA: Cotizacion.primaNeta,
            PRIMA_TOTAL: Cotizacion.primaTotal,
            ID_CIA: CotizacionP.idCIA || LP_CONFIG.idCIA,
            ASEGURADORA_CAMPANA: Cotizacion.nombreCIA || LP_CONFIG.aseguradoracampana,
            CVIC: Cotizacion.cevic,
            estado: 2
        })
            .then(() => {
                Loader(false);
                window.location.href = "thanks.html";
            })
            .catch((error) => {
                console.error(error);
                Loader(false);
                alert("No pudimos guardar tu cotización. Intenta de nuevo.");
            });
    };

    const irAlMotor = async () => {
        Loader(true);
        try {
            await VamonosalMotor(DataBase, CotizacionP);
        } catch (error) {
            console.error(error);
            Loader(false);
            alert("No pudimos preparar tu sesión para continuar con la contratación. Intenta de nuevo.");
        }
    };

    if (btnTelefono) {
        btnTelefono.addEventListener("click", () => {
            Loader(true);
            procesarCompraTelefono();
        });
    }

    if (btnTelefonoM) {
        btnTelefonoM.addEventListener("click", () => {
            Loader(true);
            procesarCompraTelefono();
        });
    }

    if (btnMotor) btnMotor.addEventListener("click", irAlMotor);
    if (btnMotorM) btnMotorM.addEventListener("click", irAlMotor);

    if (Descarga) {
        Descarga.addEventListener("click", () => {
            Descarga.disabled = true;
            Loader(true, "Descargando cotización...");
            descargarCotizacion(CotizacionP).then((result) => {
                Loader(false);
                if (result?.response) window.open(result.response);
                Descarga.disabled = false;
            });
        });
    }

    function formatCurrency(value) {
        return Number(value).toLocaleString("es-MX", { style: "currency", currency: "MXN" });
    }
});

export const descargarCotizacion = async (data) => {
    try {
        const response = await fetch("https://apis.segurointeligente.mx/api/Cotizacion/PDF", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${localStorage.getItem("tokenMAG")}`
            },
            body: JSON.stringify({
                email: "e-commerce@segurointeligente.mx",
                cpostal: data.cPostal,
                marca: data.marca,
                modelo: data.modelo,
                des: data.subMarca,
                idFpago: 1,
                idGrupo: 35,
                plan: "AMPLIA",
                Contacto: "(55) 3098 7209",
                gnp: data.cotizacionInfo[0].id
            }),
            redirect: "follow"
        });
        return response.json();
    } catch (err) {
        console.log(err);
    }
};

const GetCoberturas = async (IdprodCR) => {
    const response = await fetch(
        `https://apis.segurointeligente.mx/api/Catalogos/GetCoberturasplan?IdprodCR=${IdprodCR}`,
        {
            method: "GET",
            headers: { Authorization: "Bearer " + localStorage.getItem("tokenMAG") },
            redirect: "follow"
        }
    );
    return response.json();
};
