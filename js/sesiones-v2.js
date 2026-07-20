import { obtenerTokenMAG } from "./catalogos-mag.js";

/**
 * Tokens (no mezclar):
 * - lpSesionesToken → wsgenerico /Usuarios/Token (sesiones-v2)
 * - tokenMAG → apis.segurointeligente.mx /Autenticacion/GetToken (catalogos)
 * - tokenWS → wsservicios.gmag.com.mx (CRM Zoho, etc.)
 */
const SESIONES_V2_URL = "https://wsgenerico.segurointeligente.mx/api/sesiones-v2";
const SESIONES_V2_UPDATE_URL = `${SESIONES_V2_URL}/actualizar`;
export const SESIONES_HASH_KEY = "lpSesionHash";
const SESIONES_TOKEN_URL = "https://wsgenerico.segurointeligente.mx/Usuarios/Token";
export const SESIONES_TOKEN_KEY = "lpSesionesToken";
const IPIFY_URL = "https://api.ipify.org/?format=jsonP";
const IP_SESSION_KEY = "IPSesion";

const extraerTokenSesiones = (result) => {
    return result?.Token || result?.token || result?.accessToken || result?.response?.Token || result?.response?.token || "";
};

/** Si lpSesionesToken coincide con tokenMAG/tokenWS, el cache es invalido (401 en sesiones-v2). */
const tokenSesionesCacheInvalido = (token) => {
    if (!token) return true;
    const mag = localStorage.getItem("tokenMAG");
    const ws = localStorage.getItem("tokenWS");
    return (mag && token === mag) || (ws && token === ws);
};

export const limpiarTokenSesiones = () => {
    localStorage.removeItem(SESIONES_TOKEN_KEY);
};

export const obtenerTokenSesiones = async (forzarNuevo = false) => {
    if (!forzarNuevo) {
        const tokenGuardado = localStorage.getItem(SESIONES_TOKEN_KEY);
        if (tokenGuardado && !tokenSesionesCacheInvalido(tokenGuardado)) {
            return tokenGuardado;
        }
        if (tokenGuardado) {
            limpiarTokenSesiones();
        }
    }

    const response = await fetch(SESIONES_TOKEN_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            Username: "QA-MascotasGMX",
            Password: "f4C5V3#EqgkJ"
        }),
        redirect: "follow"
    });

    if (!response.ok) {
        const detalle = await response.text().catch(() => "");
        throw new Error(
            `Token wsgenerico (sesiones) respondio HTTP ${response.status}${detalle ? `: ${detalle.slice(0, 120)}` : ""}`
        );
    }

    const result = await response.json();
    const token = String(extraerTokenSesiones(result) || "").trim();

    if (!token) {
        throw new Error("No se pudo obtener el token de sesiones (wsgenerico).");
    }

    if (tokenSesionesCacheInvalido(token)) {
        throw new Error("El token de sesiones coincide con tokenMAG/tokenWS; revisar almacenamiento.");
    }

    localStorage.setItem(SESIONES_TOKEN_KEY, token);
    return token;
};

/** Precarga tokenMAG + lpSesionesToken + IP al cargar la LP (evita 401 y alertas falsos en submit). */
export const precargarTokensLp = async ({ forzarNuevo = true } = {}) => {
    const tareas = [
        obtenerTokenMAG(forzarNuevo).catch((error) => {
            console.warn("No se pudo precargar tokenMAG:", error);
        }),
        obtenerTokenSesiones(forzarNuevo).catch((error) => {
            console.warn("No se pudo precargar token de sesiones:", error);
        }),
        obtenerIPRemota(forzarNuevo).catch((error) => {
            console.warn("No se pudo precargar IP de sesion:", error);
        })
    ];

    await Promise.all(tareas);
};

/** Indica si crearSesion* devolvio un hash utilizable (no usar result.id). */
export const sesionLpCreadaCorrectamente = ({ hash, result } = {}) => {
    const hashResuelto =
        hash ||
        extraerHashSesion(result) ||
        obtenerHashSesion() ||
        "";

    return Boolean(String(hashResuelto).trim());
};

const fetchSesiones = async (url, options = {}, reintentar = true) => {
    const token = await obtenerTokenSesiones(false);
    if (!token) {
        throw new Error("No hay token de sesiones (wsgenerico) para autorizar la peticion.");
    }

    const headers = new Headers(options.headers || {});
    headers.set("Content-Type", "application/json");
    headers.set("Authorization", `Bearer ${token}`);

    const response = await fetch(url, { ...options, headers });

    if (response.status === 401 && reintentar) {
        limpiarTokenSesiones();
        await obtenerTokenSesiones(true);
        return fetchSesiones(url, options, false);
    }

    return response;
};

const leerJsonSesiones = async (response, contexto) => {
    if (!response.ok) {
        const detalle = await response.text().catch(() => "");
        throw new Error(
            `${contexto} respondio HTTP ${response.status}${detalle ? `: ${detalle.slice(0, 200)}` : ""}`
        );
    }

    try {
        return await response.json();
    } catch (error) {
        throw new Error(`${contexto} no devolvio JSON valido.`);
    }
};

export const normalizarTracking = (value) => {
    if (value === null || value === undefined) {
        return "";
    }

    const limpio = String(value).trim();
    if (!limpio || limpio === "N/A" || limpio.toLowerCase() === "null" || limpio.toLowerCase() === "undefined") {
        return "";
    }

    return limpio;
};

export const normalizarTelefonoMX = (value) => {
    if (value === null || value === undefined) {
        return "";
    }

    let digits = String(value).replace(/\D/g, "");

    if (digits.length === 13 && digits.startsWith("521")) {
        digits = digits.slice(3);
    } else if (digits.length === 12 && digits.startsWith("52")) {
        digits = digits.slice(2);
    } else if (digits.length === 11 && digits.startsWith("1")) {
        digits = digits.slice(1);
    } else if (digits.length > 10) {
        digits = digits.slice(-10);
    }

    return digits.slice(0, 10);
};

const extraerIpSesion = (rawValue) => {
    if (rawValue === null || rawValue === undefined) {
        return "";
    }

    const value = String(rawValue).trim();
    if (!value) {
        return "";
    }

    const match = value.match(/\b\d{1,3}(?:\.\d{1,3}){3}\b/);
    return match ? match[0] : "";
};

const guardarIpSesion = (rawValue) => {
    const ip = extraerIpSesion(rawValue);

    if (ip) {
        localStorage.setItem(IP_SESSION_KEY, ip);
    }

    return ip;
};

export const obtenerIPRemota = async (forzarNueva = false) => {
    if (!forzarNueva) {
        const ipGuardada = guardarIpSesion(localStorage.getItem(IP_SESSION_KEY) || localStorage.getItem("ipSesion"));
        if (ipGuardada) {
            return ipGuardada;
        }
    }

    const response = await fetch(IPIFY_URL, { method: "GET", redirect: "follow" });
    const result = await response.text();
    const ip = guardarIpSesion(result);

    if (!ip) {
        throw new Error("No se pudo obtener la IP de la sesion.");
    }

    return ip;
};

const obtenerAnioNacimiento = (fecha) => {
    const anio = Number(String(fecha || "").slice(0, 4));
    return Number.isFinite(anio) ? anio : null;
};

const primerValorLp = (Data, ...keys) => {
    for (const key of keys) {
        const value = Data?.[key];
        if (value !== null && value !== undefined && String(value).trim() !== "") {
            return String(value).trim();
        }
    }
    return "";
};

/** Unifica nombres de campo entre HTML (correo/Tel vs mail/celular) y sessionData manual. */
export const extraerContactoLp = (Data) => ({
    nombre: primerValorLp(Data, "nombre", "name"),
    apellidoPaterno: primerValorLp(
        Data,
        "ApPaterno",
        "apellidoPaterno",
        "apellido_paterno",
        "last",
        "lastname",
        "last_name"
    ),
    correo: primerValorLp(Data, "correo", "mail", "email"),
    telefono: normalizarTelefonoMX(
        Data.Tel ?? Data.celular ?? Data.telefono ?? Data.phone ?? ""
    ),
    codigoPostal: primerValorLp(Data, "CP", "cp", "codigoPostal", "codigo_postal"),
    fechaNacimiento: primerValorLp(Data, "FNacimiento", "age", "edad", "edadSelect", "fechaNacimiento")
});

export const validarPayloadSesionLp = (payload) => {
    if (!payload.nombre?.trim()) {
        throw new Error("Falta el nombre para crear la sesion.");
    }
    if (!payload.apellidoPaterno?.trim()) {
        throw new Error("Falta el apellido paterno para crear la sesion.");
    }
    if (!payload.correo?.trim()) {
        throw new Error("Falta el correo para crear la sesion (correo, mail o email).");
    }
    if (!payload.telefono?.trim()) {
        throw new Error("Falta el telefono para crear la sesion (Tel, celular o phone).");
    }
};

const obtenerIpSesion = () => {
    return guardarIpSesion(localStorage.getItem(IP_SESSION_KEY) || localStorage.getItem("ipSesion"));
};

const extraerHashSesion = (result) => {
    return result?.Hash || result?.hash || result?.response?.Hash || result?.response?.hash || result?.data?.Hash || result?.data?.hash || "";
};

export const guardarHashSesion = (result) => {
    const hash = extraerHashSesion(result);

    if (hash) {
        localStorage.setItem(SESIONES_HASH_KEY, hash);
    }

    return hash;
};

export const obtenerHashSesion = () => localStorage.getItem(SESIONES_HASH_KEY) || "";

const obtenerDescripcionVehiculo = (Data) => {
    const selectVersion = document.getElementById("slc-descripcionCompleta");
    const textoOpcion = selectVersion?.selectedOptions?.[0]?.text?.trim();
    if (textoOpcion && textoOpcion !== "Selecciona la versión" && textoOpcion !== "Selecciona un paquete") {
        return textoOpcion;
    }

    return Data.descripcionCompleta || "";
};

export const construirPayloadSesionLp = (Data, config) => {
    const contacto = extraerContactoLp(Data);
    const modelo = Number(Data.modelo);

    const payload = {
        marca: Data.marca || "",
        modelo: Number.isNaN(modelo) ? Data.modelo : modelo,
        submarca: Data.descripcion || "",
        descripcion: obtenerDescripcionVehiculo(Data),
        idCIA: config.idCIA,
        aseguradoracampana: config.aseguradoracampana,
        isComparator: Number(config.isComparator ?? 0),
        nombre: contacto.nombre,
        apellidoPaterno: contacto.apellidoPaterno,
        apellidoMaterno: primerValorLp(Data, "apellidoMaterno", "apellido_materno", "ApMaterno"),
        correo: contacto.correo,
        genero: Data.genero ?? Data.gener ?? "",
        telefono: contacto.telefono,
        anioNacimientoEstimado: obtenerAnioNacimiento(contacto.fechaNacimiento),
        codigoPostal: contacto.codigoPostal,
        leadsource: config.leadsource,
        utm: normalizarTracking(Data.utmc),
        gclid: normalizarTracking(Data.gclid),
        firstPage: config.firstPage,
        ipSesion: obtenerIpSesion(),
        origenSistema: config.origenSistema || "LP",
        Estrategia: config.estrategia || "SEM",
        TipoLP: config.TipoLP || "Individual"
    };

    if (config.grupo) {
        payload.grupo = config.grupo;
    }

    return payload;
};

/** Comparador multi-aseguradora (seguro-autos, comparador-de-seguros). */
export const construirPayloadSesionComparador = (Data, config) => {
    const modelo = Number(Data.model ?? Data.modelo ?? Data.modeloSelect);
    const telefono = normalizarTelefonoMX(Data.phone ?? Data.Tel ?? Data.telefono);
    const versionEl = document.getElementById("version");
    const descripcion =
        Data.version ||
        Data.descripcionCompleta ||
        versionEl?.selectedOptions?.[0]?.text?.trim() ||
        Data.descripcion ||
        Data.descripcionSelect ||
        "";

    return {
        marca: Data.marca ?? Data.marcaSelect ?? "",
        modelo: Number.isNaN(modelo) ? (Data.model ?? Data.modelo ?? Data.modeloSelect ?? "") : modelo,
        submarca: Data.submarca ?? Data.descripcion ?? Data.descripcionSelect ?? "",
        descripcion,
        idCIA: config.idCIA ?? 0,
        aseguradoracampana: config.aseguradoracampana ?? "COMPARADOR",
        isComparator: 1,
        nombre: Data.name ?? Data.nombre ?? "",
        apellidoPaterno:
            Data.last ??
            Data.lastname ??
            Data.last_name ??
            Data.apellido_paterno ??
            Data.ApPaterno ??
            "",
        apellidoMaterno: Data.apellido_materno ?? Data.apellidoMaterno ?? "",
        correo: Data.mail ?? Data.correo ?? Data.email ?? "",
        genero: Data.gener ?? Data.genero ?? Data.generoSelect ?? "",
        telefono,
        anioNacimientoEstimado: obtenerAnioNacimiento(
            Data.age ?? Data.FNacimiento ?? Data.edadSelect ?? Data.edad
        ),
        codigoPostal: Data.cp ?? Data.codigo_postal ?? Data.CP ?? "",
        leadsource: config.leadsource,
        utm: normalizarTracking(Data.utmc),
        gclid: normalizarTracking(Data.gclid),
        firstPage: config.firstPage,
        ipSesion: obtenerIpSesion(),
        origenSistema: config.origenSistema ?? "LP",
        Estrategia: config.estrategia ?? "SEM"
    };
};

export const crearSesionComparador = async (Data, config) => {
    if (config && config.__ready) {
        try { await config.__ready; } catch (error) { console.warn("No se pudo sincronizar la config de la LP con el catalogo:", error); }
    }
    await precargarTokensLp({ forzarNuevo: false });

    const response = await fetchSesiones(SESIONES_V2_URL, {
        method: "POST",
        body: JSON.stringify(construirPayloadSesionComparador(Data, config)),
        redirect: "follow"
    });

    const result = await leerJsonSesiones(response, "sesiones-v2 (comparador)");

    if (result?.Adelante === false) {
        throw new Error(result?.Message || "No se pudo crear la sesion inicial.");
    }

    const hash = guardarHashSesion(result);
    if (!hash) {
        throw new Error("La respuesta de sesiones-v2 no devolvio hash.");
    }

    return { hash, result };
};

export const crearSesionInicial = async (Data, config) => {
    if (config && config.__ready) {
        try { await config.__ready; } catch (error) { console.warn("No se pudo sincronizar la config de la LP con el catalogo:", error); }
    }
    await precargarTokensLp({ forzarNuevo: false });

    const payload = construirPayloadSesionLp(Data, config);
    validarPayloadSesionLp(payload);

    const response = await fetchSesiones(SESIONES_V2_URL, {
        method: "POST",
        body: JSON.stringify(payload),
        redirect: "follow"
    });

    const result = await leerJsonSesiones(response, "sesiones-v2");

    if (result?.Adelante === false) {
        throw new Error(result?.Message || "No se pudo crear la sesion inicial.");
    }

    const hash = guardarHashSesion(result);
    if (!hash) {
        throw new Error("La respuesta de sesiones-v2 no devolvio hash.");
    }

    return { hash, result };
};

export const actualizarSesionSeleccionada = async (config, data) => {
    const hash = obtenerHashSesion();
    const idCotMag = Number(data?.ID_COT_MAG ?? data?.idCotMag ?? data?.id ?? 0);

    if (!hash) {
        throw new Error("No existe hash de sesion para actualizar la cotizacion.");
    }

    if (!idCotMag) {
        throw new Error("No existe idCotMag para la cotizacion seleccionada.");
    }

    const response = await fetchSesiones(SESIONES_V2_UPDATE_URL, {
        method: "POST",
        body: JSON.stringify({
            hash,
            idCotMag,
            primaNeta: Number(data?.PRIMA_NETA ?? data?.primaNeta ?? 0),
            primaTotal: Number(data?.PRIMA_TOTAL ?? data?.primaTotal ?? 0),
            idCIA: Number(data?.ID_CIA ?? data?.idCIA ?? data?.idCia ?? config.idCIA),
            aseguradoracampana: data?.ASEGURADORA_CAMPANA ?? data?.aseguradoracampana ?? config.aseguradoracampana,
            cevic: data?.CVIC ?? data?.cevic ?? "",
            estado: Number(data?.estado ?? 2)
        }),
        redirect: "follow"
    });

    const result = await leerJsonSesiones(response, "sesiones-v2/actualizar");

    if (result?.Adelante === false || result?.SesionActualizada === false) {
        throw new Error(result?.Message || "No se pudo actualizar la sesion seleccionada.");
    }

    guardarHashSesion(result);
    return { hash: obtenerHashSesion(), result };
};

export const actualizarSesionDesdeCotizacion = async (config, cotizacionInfo, cotizacionResponse) => {
    const info = cotizacionInfo?.cotizacionInfo?.[0] ?? cotizacionInfo;

    if (!info) {
        throw new Error("No hay informacion de cotizacion para actualizar la sesion.");
    }

    return actualizarSesionSeleccionada(config, {
        ID_COT_MAG: info.id,
        PRIMA_NETA: info.primaNeta,
        PRIMA_TOTAL: info.primaTotal,
        ID_CIA: cotizacionResponse?.idCIA ?? info.idCia ?? config.idCIA,
        ASEGURADORA_CAMPANA: info.nombreCIA || config.aseguradoracampana,
        CVIC: info.cevic,
        estado: 2
    });
};
