/**
 * Robust JSON extraction helper.
 * If the server emits PHP warnings/errors before the JSON, this extracts the valid JSON part.
 */
function safeFetch(url, options = {}) {
    return fetch(url, options)
        .then(res => res.text())
        .then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                // Find where the JSON starts ({ or [)
                const start = text.indexOf('{') > -1 ? text.indexOf('{') : text.indexOf('[');
                if (start > -1) {
                    try {
                        return JSON.parse(text.substring(start));
                    } catch (e2) {
                        console.error("Critical JSON Parse Error:", text);
                        throw e2;
                    }
                }
                throw e;
            }
        });
}
