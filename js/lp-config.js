const LP_CONFIG_API = "https://wsgenerico.segurointeligente.mx/api/lpconfig";
const LP_KEY_FALLBACK = "lp-axasegurosenlinea-sem";

/**
 * Fallback local si resolve/key no responden.
 * En producción estos valores deben venir del catalogo lpconfig.
 */
export const LP_CONFIG = {
    idCIA: 7,
    isComparator: 0,
    aseguradoracampana: "AXA",
    leadsource: "LP-AUTO-AXASEGUROSENLINEA-SEM",
    firstPage: typeof window !== "undefined" ? window.location.href.split("?")[0] : "",
    estrategia: "SEM",
    TipoLP: "Individual",
    grupo: "",
    origenSistema: "LP",
    ecommerceBaseUrl: "https://segurointeligente.mx/e-commerce"
};

const tomarValor = (...values) => {
    for (const value of values) {
        if (value !== null && value !== undefined && String(value).trim() !== "") {
            return value;
        }
    }
    return "";
};

const aplicarConfigRemota = (data) => {
    if (!data) return;

    const template = data.template || {};
    const variables = data.variables || {};

    LP_CONFIG.leadsource = tomarValor(template.leadsource, data.leadsource, LP_CONFIG.leadsource);
    LP_CONFIG.aseguradoracampana = tomarValor(
        template.aseguradoracampana,
        data.aseguradoracampana,
        LP_CONFIG.aseguradoracampana
    );
    LP_CONFIG.firstPage = tomarValor(template.firstPage, data.firstPage, window.location.href.split("?")[0]);
    LP_CONFIG.estrategia = tomarValor(template.Estrategia, data.Estrategia, LP_CONFIG.estrategia);
    LP_CONFIG.TipoLP = tomarValor(template.TipoLP, data.TipoLP, LP_CONFIG.TipoLP);
    LP_CONFIG.grupo = tomarValor(template.grupo, data.grupo, LP_CONFIG.grupo);
    LP_CONFIG.origenSistema = tomarValor(template.origenSistema, data.origenSistema, LP_CONFIG.origenSistema);
    LP_CONFIG.lpKey = tomarValor(data.lpKey, LP_CONFIG.lpKey);

    const idCIA = tomarValor(variables.idCIA, template.idCIA, data.idCIA);
    if (idCIA !== "") {
        LP_CONFIG.idCIA = Number(idCIA);
    }

    const isComparator = tomarValor(variables.isComparator, template.isComparator);
    if (isComparator !== "") {
        LP_CONFIG.isComparator = Number(isComparator);
    } else {
        LP_CONFIG.isComparator = 0;
    }

    const ecommerceBaseUrl = tomarValor(variables.ecommerceBaseUrl, template.ecommerceBaseUrl);
    if (ecommerceBaseUrl) {
        LP_CONFIG.ecommerceBaseUrl = String(ecommerceBaseUrl).replace(/\/$/, "");
    }
};

const resolverPorPageUrl = async () => {
    const pageUrl = encodeURIComponent(window.location.href.split("#")[0]);
    const respuesta = await fetch(`${LP_CONFIG_API}/resolve?pageUrl=${pageUrl}`, {
        method: "GET",
        headers: { "Content-Type": "application/json" },
        redirect: "follow"
    });

    if (!respuesta.ok) {
        throw new Error(`resolve HTTP ${respuesta.status}`);
    }

    const json = await respuesta.json();
    if (!json?.ok || !json?.data) {
        throw new Error("resolve sin data");
    }

    return json.data;
};

const resolverPorKey = async () => {
    const respuesta = await fetch(`${LP_CONFIG_API}/key/${encodeURIComponent(LP_KEY_FALLBACK)}`, {
        method: "GET",
        headers: { "Content-Type": "application/json" },
        redirect: "follow"
    });

    if (!respuesta.ok) {
        throw new Error(`key HTTP ${respuesta.status}`);
    }

    const json = await respuesta.json();
    if (!json?.ok || !json?.data) {
        throw new Error("key sin data");
    }

    return json.data;
};

export const lpConfigReady = (async () => {
    try {
        let data = null;

        try {
            data = await resolverPorPageUrl();
        } catch (errorResolve) {
            console.warn("LP config resolve falló, se intenta por lpKey:", errorResolve);
            data = await resolverPorKey();
        }

        aplicarConfigRemota(data);
    } catch (error) {
        console.warn("LP config: no se pudo cargar del catalogo, se usa config local.", error);
        LP_CONFIG.firstPage = window.location.href.split("?")[0];
    }

    return LP_CONFIG;
})();

LP_CONFIG.__ready = lpConfigReady;
