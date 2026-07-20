function parametroURL(_par) {
    let _p = null;
    if (!location.search) return _p;

    location.search
        .substr(1)
        .split("&")
        .forEach(function (pllv) {
            const s = pllv.split("=");
            const ll = s[0];
            const v = s[1] && decodeURIComponent(s[1]);
            if (ll == _par) {
                if (_p == null) {
                    _p = v;
                } else if (Array.isArray(_p)) {
                    _p.push(v);
                } else {
                    _p = [_p, v];
                }
            }
        });

    return _p;
}

const utmcEl = document.getElementById("utmc");
const gclidEl = document.getElementById("gclid");

const campana1 = parametroURL("utm_campaign");
if (utmcEl) {
    utmcEl.value =
        campana1 === null || campana1 === "" || campana1 === undefined ? "N/A" : campana1;
}

const campana2 = parametroURL("gclid");
if (gclidEl) {
    gclidEl.value =
        campana2 === null ||
        campana2 === "" ||
        campana2 === undefined ||
        campana2 === "null"
            ? "N/A"
            : campana2;
}
