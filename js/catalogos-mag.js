const API_BASE = "https://apis.segurointeligente.mx/api";
export const TOKEN_MAG_KEY = "tokenMAG";

/** Token de apis.segurointeligente.mx (catalogos/cotizacion). No confundir con lpSesionesToken (wsgenerico) ni tokenWS (CRM). */
export const extraerTokenDeRespuesta = (result) =>
    result?.token ?? result?.Token ?? result?.access_token ?? null;

const tokenMagCacheInvalido = (token) => {
    if (!token) return true;
    const sesiones = localStorage.getItem("lpSesionesToken");
    const ws = localStorage.getItem("tokenWS");
    return (sesiones && token === sesiones) || (ws && token === ws);
};

export const obtenerTokenMAG = async (forzarNuevo = false) => {
    if (!forzarNuevo) {
        const tokenGuardado = localStorage.getItem(TOKEN_MAG_KEY);
        if (tokenGuardado && !tokenMagCacheInvalido(tokenGuardado)) {
            return tokenGuardado;
        }
        if (tokenGuardado) {
            localStorage.removeItem(TOKEN_MAG_KEY);
        }
    }

    const response = await fetch(`${API_BASE}/Autenticacion/GetToken`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            usuario: "segurointeligente",
            contrasena: "Gmag2023*"
        }),
        redirect: "follow"
    });

    if (!response.ok) {
        throw new Error(`GetToken respondio HTTP ${response.status}`);
    }

    const result = await response.json();
    const token = extraerTokenDeRespuesta(result);

    if (!token) {
        throw new Error("No se pudo obtener el token de catalogos MAG.");
    }

    localStorage.setItem(TOKEN_MAG_KEY, token);
    return token;
};

export const fetchCatalogoMAG = async (url, options = {}, reintento = true) => {
    const token = await obtenerTokenMAG();
    const headers = new Headers(options.headers || {});

    if (!headers.has("Content-Type") && options.body) {
        headers.set("Content-Type", "application/json");
    }

    headers.set("Authorization", `Bearer ${token}`);

    const response = await fetch(url, { ...options, headers });

    if (response.status === 401 && reintento) {
        await obtenerTokenMAG(true);
        return fetchCatalogoMAG(url, options, false);
    }

    return response;
};

export const obtenerModelos = async (nombreMarca, rango = "2005") => {
    const response = await fetchCatalogoMAG(`${API_BASE}/Catalogos/GetModelos`, {
        method: "POST",
        body: JSON.stringify({ nombreMarca, rango }),
        redirect: "follow"
    });

    if (!response.ok) {
        throw new Error(`GetModelos respondio HTTP ${response.status}`);
    }

    const data = await response.json();

    if (data?.message && data.message !== "Ok") {
        throw new Error(data.message || "GetModelos no devolvio message Ok.");
    }

    return data;
};

export const obtenerLineaSubMarca = async (nombreMarca, anio) => {
    const response = await fetchCatalogoMAG(`${API_BASE}/Catalogos/GetLineaSubMarca`, {
        method: "POST",
        body: JSON.stringify({ nombreMarca, anio }),
        redirect: "follow"
    });

    if (!response.ok) {
        throw new Error(`GetLineaSubMarca respondio HTTP ${response.status}`);
    }

    const data = await response.json();

    if (data?.message && data.message !== "Ok") {
        throw new Error(data.message || "GetLineaSubMarca no devolvio message Ok.");
    }

    return data;
};

export const obtenerDescripcionesHom = async (marca, modelo, submarca) => {
    const url =
        `${API_BASE}/Catalogos/GetDescripcionesHom` +
        `?marca=${encodeURIComponent(marca)}` +
        `&modelo=${encodeURIComponent(modelo)}` +
        `&submarca=${encodeURIComponent(submarca)}`;

    const response = await fetchCatalogoMAG(url, {
        method: "GET",
        redirect: "follow"
    });

    if (!response.ok) {
        throw new Error(`GetDescripcionesHom respondio HTTP ${response.status}`);
    }

    return response.json();
};

/** { message: "Ok", response: [{ nombre: "ACURA" }, ...] } */
export const extraerListaMarcas = (data) => {
    const list = data?.response ?? (Array.isArray(data) ? data : null);

    if (!Array.isArray(list)) {
        throw new Error("La respuesta de GetMarcas no contiene un arreglo en response.");
    }

    return list;
};

export const obtenerNombreMarca = (item) => {
    return String(item?.nombre ?? item?.Marca ?? item?.marca ?? item ?? "").trim();
};

export const obtenerMarcasCatalogo = async () => {
    const response = await fetchCatalogoMAG(`${API_BASE}/Catalogos/GetMarcas`, {
        method: "GET",
        redirect: "follow"
    });

    if (!response.ok) {
        throw new Error(`GetMarcas respondio HTTP ${response.status}`);
    }

    const data = await response.json();

    if (data?.message && data.message !== "Ok") {
        throw new Error(data.message || "GetMarcas no devolvio message Ok.");
    }

    return extraerListaMarcas(data);
};

/** Compatibilidad con selects que esperaban { Marca } del servicio Railway. */
export const normalizarMarcasLegacy = (marcas) => {
    return marcas
        .map((item) => ({ Marca: obtenerNombreMarca(item) }))
        .filter((item) => item.Marca);
};

export const poblarSelectConMarcas = (selectElement, marcas, options = {}) => {
    const placeholder = options.placeholder ?? "Selecciona una Marca";
    const legacy = normalizarMarcasLegacy(marcas);

    let html = `<option value='' selected disabled>${placeholder}</option>`;
    legacy.forEach((item) => {
        html += `<option value="${item.Marca}">${item.Marca}</option>`;
    });

    selectElement.innerHTML = html;
    return legacy;
};

export const cargarSelectMarcas = async (selectElement, options = {}) => {
    if (!selectElement) {
        throw new Error("No se encontro el select de marcas.");
    }

    const marcas = await obtenerMarcasCatalogo();
    poblarSelectConMarcas(selectElement, marcas, options);

    if (options.focus && window.innerWidth > 880) {
        selectElement.focus();
    }

    return normalizarMarcasLegacy(marcas);
};

/** Para helpers que agregan <option> con createElement (comparadores). */
export const agregarOpcionesMarcas = (selectElement, marcas) => {
    normalizarMarcasLegacy(marcas).forEach((item) => {
        const option = document.createElement("option");
        option.value = item.Marca;
        option.textContent = item.Marca;
        selectElement.appendChild(option);
    });
};

const escapeHtmlOpcion = (value = "") =>
    String(value).replace(/[&<>"']/g, (char) =>
        ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[char])
    );

/** Versiones homologadas (comparador / hot-sale). Reemplaza GetCevic cuando no devuelve datos. */
export const cargarSelectVersionesHom = async (
    selectElement,
    marca,
    modelo,
    submarca,
    options = {}
) => {
    if (!selectElement) {
        throw new Error("No se encontro el select de versiones.");
    }

    const placeholder = options.placeholder ?? "Selecciona la versión";
    const data = await obtenerDescripcionesHom(marca, modelo, submarca);
    const items = Array.isArray(data?.response) ? data.response : [];
    const mensaje = String(data?.message ?? "").toLowerCase();

    if (
        items.length === 0 ||
        (data?.message && data.message !== "Ok" && mensaje.includes("no se pudo"))
    ) {
        if (options.fallbackEspecial !== false) {
            selectElement.innerHTML = "<option value='Especial' selected>Especial</option>";
        } else {
            selectElement.innerHTML = `<option value="" selected disabled>${placeholder}</option>`;
        }
        return items;
    }

    let html = `<option value="" selected disabled>${placeholder}</option>`;
    items.forEach((item) => {
        const texto = escapeHtmlOpcion(item?.descripcionG ?? item?.descripcion ?? item?.nombre ?? "");
        const valor = escapeHtmlOpcion(item?.cevic ?? item?.cvevic ?? texto);
        if (texto) {
            html += `<option value="${valor}">${texto}</option>`;
        }
    });

    selectElement.innerHTML = html;

    if (options.focus && window.innerWidth > 880) {
        selectElement.focus();
    }

    return items;
};
