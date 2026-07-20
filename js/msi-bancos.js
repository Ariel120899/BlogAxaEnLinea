/**
 * Catálogo genérico MSI (reemplaza Railway Catalogos/BancosxAseguradoraMSI).
 * Formato compatible con el consumo existente: { response: [{ banco: { nombre }, MSI }] }.
 */
export const MSI_BANCOS_GENERICO = {
    response: [
        { banco: { nombre: "Banamex" }, MSI: "3, 6" },
        { banco: { nombre: "BBVA" }, MSI: "3, 6" },
        { banco: { nombre: "Santander" }, MSI: "3, 6" }
    ]
};

/** @param {number|string} _idAseguradora ignorado; se mantiene la firma anterior MSIxBancos(idCIA) */
export const MSIxBancos = async (_idAseguradora) => {
    return Promise.resolve(MSI_BANCOS_GENERICO);
};
