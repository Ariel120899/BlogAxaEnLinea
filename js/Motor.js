import { actualizarSesionSeleccionada, obtenerHashSesion } from './sesiones-v2.js';
import { LP_CONFIG } from './lp-config.js';

export async function VamonosalMotor(data, dataAdi) {
    const cotInfo = dataAdi.cotizacionInfo[0];

    const { hash } = await actualizarSesionSeleccionada(LP_CONFIG, {
        ID_COT_MAG: cotInfo.id,
        PRIMA_NETA: cotInfo.primaNeta,
        PRIMA_TOTAL: cotInfo.primaTotal,
        ID_CIA: dataAdi.idCIA || LP_CONFIG.idCIA,
        ASEGURADORA_CAMPANA: cotInfo.nombreCIA || LP_CONFIG.aseguradoracampana,
        CVIC: cotInfo.cevic,
        estado: 2
    });

    const base = (LP_CONFIG.ecommerceBaseUrl || "https://segurointeligente.mx/e-commerce").replace(/\/$/, "");
    const hashFinal = hash || obtenerHashSesion();

    localStorage.setItem("Ingreso", "Motor");
    localStorage.setItem("ecommerce", true);
    window.location.href = `${base}/#/?hash=${encodeURIComponent(hashFinal)}`;
}

export async function Loader(valor, mensaje) {
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

        let mensajes = ["Enviando Cotización", "Mandando descuentos", "Preparando contratación"];
        if (mensaje != undefined) {
            mensajes = [mensaje];
        }

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

export const Generador = (Data, dataAdi) => {
    const firstPage = LP_CONFIG.firstPage || window.location.href.split("?")[0];

    return fetch("https://wsservicios.gmag.com.mx/ZoohoTools/CRM/CrearProspectosSI", {
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
                description:
                    "El usuario cotizo un vehiculo con las siguientes especificaciones: Marca: " +
                    Data.marca +
                    " Modelo: " +
                    Data.modelo +
                    " Submarca: " +
                    Data.descripcion +
                    " CP: " +
                    Data.CP +
                    " Genero: " +
                    Data.genero +
                    " Fecha de Nacimiento: " +
                    Data.FNacimiento +
                    " Correo: " +
                    Data.correo +
                    " CVIC: " +
                    dataAdi.cevic +
                    " Prima Total: " +
                    dataAdi.primaTotal +
                    " Descripcion Completa: " +
                    dataAdi.cotizacionInfo[0].descripcionCompleta,
                first_Name: Data.nombre,
                full_Name: Data.nombre + " " + Data.ApPaterno,
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
        .then((result) => result.data)
        .catch((error) => console.error(error));
};
