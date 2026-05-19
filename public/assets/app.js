async function apiPost(url, body) {
  const base = (typeof window !== "undefined" && window.__BASE__) ? window.__BASE__ : "";
  const fullUrl = url.startsWith("/") ? `${base}${url}` : url;
  const res = await fetch(fullUrl, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "same-origin",
    body: JSON.stringify(body ?? {}),
  });

  const data = await res.json().catch(() => null);
  if (!res.ok) {
    const msg = data?.error || `Request failed (${res.status})`;
    throw new Error(msg);
  }
  return data;
}

function setMsg(el, text, type) {
  if (!el) return;
  el.className = `msg ${type || ""}`.trim();
  el.textContent = text;
  el.style.display = text ? "" : "none";
}

window.PlacementApp = { apiPost, setMsg };

