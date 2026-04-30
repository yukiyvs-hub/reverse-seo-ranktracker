<?php require_once 'auth_check.php'; ?>
<!DOCTYPE html>
<html lang="ja" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reverse SEO Rank Checker</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700;800&family=DM+Mono:wght@400;500&display=swap');

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root, [data-theme="dark"] {
    --bg: #0a0a0f; --bg2: #111118; --bg3: #1a1a24; --border: #2a2a38;
    --accent: #7c6aff; --accent3: #6affb8; --text: #e8e8f0; --text2: #c0c0d0;
    --text3: #7a7a90; --danger: #ff4a6a; --positive: #6affb8; --negative: #ff6a8a;
    --shadow: rgba(0,0,0,0.3); --mono: 'DM Mono', monospace; --sans: 'Noto Sans JP', sans-serif;
  }
  [data-theme="light"] {
    --bg: #f4f4f7; --bg2: #ffffff; --bg3: #eeeef3; --border: #dddde8;
    --accent: #6a58f0; --accent3: #00b87a; --text: #1a1a2e; --text2: #5a5a78;
    --text3: #9a9ab8; --danger: #e8335a; --positive: #00b87a; --negative: #e8335a;
    --shadow: rgba(0,0,0,0.08); --mono: 'DM Mono', monospace; --sans: 'Noto Sans JP', sans-serif;
  }

  html, body { height: 100%; overflow: hidden; background: var(--bg); color: var(--text); font-family: var(--sans); transition: background 0.2s, color 0.2s; }
  .app { display: grid; grid-template-columns: 280px 1fr; grid-template-rows: 56px 1fr; height: 100vh; }

  .header { grid-column: 1/-1; display: flex; align-items: center; justify-content: space-between; padding: 0 24px; border-bottom: 1px solid var(--border); background: var(--bg); z-index: 10; transition: background 0.2s, border-color 0.2s; }
  .header-logo { display: flex; align-items: center; gap: 10px; }
  .header-logo span { color: var(--text2); font-weight: 400; font-size: 14px; }
  .header-right { display: flex; align-items: center; gap: 12px; }
  .badge { font-family: var(--mono); font-size: 11px; padding: 3px 8px; border-radius: 4px; background: var(--bg3); color: var(--text2); border: 1px solid var(--border); }
  .theme-toggle-wrap { display: flex; align-items: center; gap: 8px; }
  .theme-label { font-size: 11px; color: var(--text3); font-weight: 600; font-family: var(--mono); }
  .theme-toggle { width: 36px; height: 20px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg3); cursor: pointer; position: relative; transition: all 0.2s; flex-shrink: 0; }
  .theme-toggle::after { content: ''; position: absolute; top: 2px; left: 2px; width: 14px; height: 14px; border-radius: 50%; background: var(--accent); transition: transform 0.2s; }
  [data-theme="light"] .theme-toggle::after { transform: translateX(16px); }

  .sidebar { background: var(--bg2); border-right: 1px solid var(--border); display: flex; flex-direction: column; overflow: hidden; transition: background 0.2s, border-color 0.2s; }
  .sidebar-head { padding: 14px 16px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
  .sidebar-title { font-size: 11px; font-weight: 600; letter-spacing: 0.12em; color: var(--text2); text-transform: uppercase; }
  .btn-add { width: 26px; height: 26px; border-radius: 6px; background: var(--accent); border: none; cursor: pointer; color: #fff; font-size: 18px; display: flex; align-items: center; justify-content: center; }
  .sidebar-scroll { flex: 1; overflow-y: auto; padding: 8px; }
  .client-block { margin-bottom: 4px; }
  .client-row { display: flex; align-items: center; gap: 6px; padding: 8px 10px; border-radius: 8px; cursor: pointer; transition: background 0.15s; }
  .client-row:hover { background: var(--bg3); }
  .client-chevron { font-size: 10px; color: var(--text3); transition: transform 0.2s; width: 12px; }
  .client-chevron.open { transform: rotate(90deg); }
  .client-name { font-size: 13px; font-weight: 700; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .client-actions { display: flex; gap: 4px; opacity: 0; transition: opacity 0.15s; }
  .client-row:hover .client-actions { opacity: 1; }
  .icon-btn { background: none; border: none; cursor: pointer; font-size: 12px; padding: 2px 4px; border-radius: 4px; color: var(--text2); }
  .icon-btn.danger:hover { color: var(--danger); }
  .keyword-list { padding-left: 18px; }
  .keyword-row { display: flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 6px; cursor: pointer; transition: background 0.15s; margin-bottom: 1px; border-left: 2px solid transparent; }
  .keyword-row:hover { background: var(--bg3); }
  .keyword-row.active { background: var(--bg3); border-left-color: var(--accent); }
  .keyword-text { font-size: 12px; font-family: var(--mono); flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text2); }
  .keyword-row.active .keyword-text { color: var(--accent); }
  .keyword-actions { display: flex; gap: 4px; opacity: 0; transition: opacity 0.15s; }
  .keyword-row:hover .keyword-actions { opacity: 1; }
  .kw-add-btn { display: flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 6px; cursor: pointer; color: var(--text3); font-size: 11px; font-weight: 600; transition: color 0.15s; }
  .kw-add-btn:hover { color: var(--accent3); }
  .sidebar-footer { padding: 12px 16px; border-top: 1px solid var(--border); }
  .btn-trigger { width: 100%; padding: 8px; border-radius: 8px; background: transparent; border: 1px solid var(--border); color: var(--text2); font-family: var(--sans); font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.15s; }
  .btn-trigger:hover { border-color: var(--accent3); color: var(--accent3); }

  .main { overflow-y: auto; display: flex; flex-direction: column; }
  .empty-state { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; color: var(--text3); }
  .empty-icon { font-size: 48px; opacity: 0.3; }
  .dashboard { padding: 24px; display: flex; flex-direction: column; gap: 20px; }
  .dash-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
  .dash-client { font-size: 12px; color: var(--text2); margin-bottom: 4px; font-weight: 600; }
  .dash-title { font-size: 22px; font-weight: 800; font-family: var(--mono); color: var(--accent); }
  .dash-actions { display: flex; gap: 8px; }
  .period-bar { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
  .period-label { font-size: 11px; color: var(--text2); margin-right: 4px; }
  .period-btn { padding: 5px 12px; border-radius: 6px; border: 1px solid var(--border); background: transparent; color: var(--text2); font-family: var(--sans); font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.15s; }
  .period-btn:hover, .period-btn.active { background: var(--accent); border-color: var(--accent); color: #fff; }
  .date-input { padding: 5px 10px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg3); color: var(--text); font-family: var(--mono); font-size: 12px; }
  .btn { padding: 8px 16px; border-radius: 8px; border: none; font-family: var(--sans); font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.15s; display: inline-flex; align-items: center; gap: 6px; }
  .btn-primary { background: var(--accent); color: #fff; }
  .btn-primary:hover { opacity: 0.85; }
  .btn-secondary { background: var(--bg3); color: var(--text); border: 1px solid var(--border); }
  .btn-secondary:hover { border-color: var(--accent); color: var(--accent); }
  .btn:disabled { opacity: 0.4; cursor: not-allowed; }

  .chart-wrap { background: var(--bg2); border: 1px solid var(--border); border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px var(--shadow); }
  .chart-title { font-size: 12px; font-weight: 600; color: var(--text2); letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 16px; }
  .chart-svg-wrap { width: 100%; }
  .chart-legend { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
  .legend-item { display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--text2); font-family: var(--mono); padding: 4px 10px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg3); cursor: pointer; transition: all 0.15s; user-select: none; }
  .legend-item:hover { border-color: var(--text3); }
  .legend-item.hidden { opacity: 0.3; text-decoration: line-through; }
  .legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

  .rank-table-wrap { background: var(--bg2); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px var(--shadow); }
  .rank-table-head { padding: 14px 20px; border-bottom: 1px solid var(--border); }
  .rank-table-title { font-size: 12px; font-weight: 600; color: var(--text2); letter-spacing: 0.08em; text-transform: uppercase; }
  table { width: 100%; border-collapse: collapse; }
  th { padding: 10px 20px; text-align: left; font-size: 11px; color: var(--text3); font-weight: 600; border-bottom: 1px solid var(--border); background: var(--bg3); }
  td { padding: 12px 20px; font-size: 13px; border-bottom: 1px solid var(--border); }
  tr:last-child td { border-bottom: none; }
  tr:hover td { background: var(--bg3); }
  .rank-num { font-family: var(--mono); font-size: 20px; font-weight: 500; }
  .rank-1-10 { color: var(--accent3); }
  .rank-11-30 { color: var(--accent); }
  .rank-31-50 { color: var(--text2); }
  .rank-out { color: var(--text3); font-size: 12px; }
  .url-text { font-family: var(--mono); font-size: 12px; color: var(--text2); }
  .type-badge { display: inline-block; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 4px; letter-spacing: 0.06em; }
  .type-badge.positive { background: rgba(106,255,184,0.15); color: var(--positive); border: 1px solid rgba(106,255,184,0.3); }
  .type-badge.negative { background: rgba(255,106,138,0.15); color: var(--negative); border: 1px solid rgba(255,106,138,0.3); }

  .url-entry-list { display: flex; flex-direction: column; gap: 8px; }
  .url-entry { display: flex; gap: 8px; align-items: center; }
  .url-entry input[type="text"] { flex: 1; padding: 8px 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg3); color: var(--text); font-family: var(--mono); font-size: 12px; }
  .url-entry input[type="text"]:focus { outline: none; border-color: var(--accent); }
  .url-entry select { padding: 8px 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg3); color: var(--text); font-family: var(--sans); font-size: 12px; cursor: pointer; }
  .url-entry select:focus { outline: none; border-color: var(--accent); }
  .url-entry .remove-btn { background: none; border: none; color: var(--text3); cursor: pointer; font-size: 16px; padding: 4px; transition: color 0.15s; }
  .url-entry .remove-btn:hover { color: var(--danger); }
  .btn-add-url { display: flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 8px; border: 1px dashed var(--border); background: transparent; color: var(--text3); font-family: var(--sans); font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.15s; margin-top: 4px; width: 100%; }
  .btn-add-url:hover { border-color: var(--accent); color: var(--accent); }

  .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 100; backdrop-filter: blur(4px); }
  .modal { background: var(--bg2); border: 1px solid var(--border); border-radius: 16px; padding: 28px; width: 560px; max-width: 95vw; max-height: 90vh; overflow-y: auto; box-shadow: 0 8px 32px var(--shadow); }
  .modal-title { font-size: 18px; font-weight: 800; margin-bottom: 20px; }
  .form-group { margin-bottom: 16px; }
  .form-label { font-size: 11px; font-weight: 600; color: var(--text2); letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 6px; display: block; }
  .form-input { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg3); color: var(--text); font-family: var(--mono); font-size: 13px; }
  .form-input:focus { outline: none; border-color: var(--accent); }
  .form-hint { font-size: 11px; color: var(--text3); margin-top: 4px; }
  .modal-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 20px; }

  .toast { position: fixed; bottom: 24px; right: 24px; background: var(--bg2); border: 1px solid var(--border); border-radius: 10px; padding: 12px 18px; font-size: 13px; z-index: 200; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 16px var(--shadow); }
  .toast.success { border-color: var(--accent3); color: var(--accent3); }
  .toast.error { border-color: var(--danger); color: var(--danger); }
  .loading { display: inline-block; width: 14px; height: 14px; border: 2px solid var(--border); border-top-color: var(--accent); border-radius: 50%; animation: spin 0.6s linear infinite; }
  @keyframes spin { to { transform: rotate(360deg); } }
  ::-webkit-scrollbar { width: 4px; }
  ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
</style>
</head>
<body>
<div class="app">
  <header class="header">
    <div class="header-logo">
      <img id="logoImg" src="https://raw.githubusercontent.com/yukiyvs-hub/reverse-seo-ranktracker/main/assets/images/VS_logo_W_背景無.png" style="height:80px;object-fit:contain;margin-top:4px;" />
      <span>/ Reverse SEO Rank Checker</span>
    </div>
    <div class="header-right">
      <span class="badge" id="balanceBadge">残高：-</span>
      <span class="badge" id="lastUpdated">-</span>
      <a href="logout.php" style="padding:6px 14px;border-radius:8px;border:1px solid var(--border);color:var(--text2);font-size:12px;font-weight:600;text-decoration:none;transition:all 0.15s;" onmouseover="this.style.borderColor='var(--danger)';this.style.color='var(--danger)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text2)'">ログアウト</a>
<div class="theme-toggle-wrap">
        <span class="theme-label" id="themeLabel">DARK</span>
        <button class="theme-toggle" onclick="toggleTheme()" title="ライト/ダーク切り替え"></button>
      </div>
    </div>
  </header>
  <aside class="sidebar">
    <div class="sidebar-head">
      <span class="sidebar-title">クライアント</span>
      <button class="btn-add" onclick="openClientModal(null)">+</button>
    </div>
    <div class="sidebar-scroll" id="clientList"></div>
    <div class="sidebar-footer">
      <button class="btn-trigger" onclick="showToast('cron設定はサーバー側で行ってください', 'success')">⏱ 自動実行トリガー設定</button>
    </div>
  </aside>
  <main class="main" id="main">
    <div class="empty-state">
      <div class="empty-icon">📊</div>
      <div style="font-size:14px">クライアントを選択してください</div>
    </div>
  </main>
</div>

<div id="clientModal" class="modal-overlay" style="display:none" onclick="if(event.target===this)closeClientModal()">
  <div class="modal">
    <div class="modal-title" id="clientModalTitle">クライアント追加</div>
    <div class="form-group">
      <label class="form-label">クライアント名</label>
      <input class="form-input" id="clientName" placeholder="例：株式会社〇〇" />
    </div>
    <div class="modal-actions">
      <button class="btn btn-secondary" onclick="closeClientModal()">キャンセル</button>
      <button class="btn btn-primary" onclick="saveClient()">保存</button>
    </div>
  </div>
</div>

<div id="kwModal" class="modal-overlay" style="display:none" onclick="if(event.target===this)closeKwModal()">
  <div class="modal">
    <div class="modal-title" id="kwModalTitle">キーワード追加</div>
    <div class="form-group">
      <label class="form-label">計測キーワード</label>
      <input class="form-input" id="kwText" placeholder="例：株式会社〇〇 評判" />
      <div class="form-hint">このキーワードでGoogle検索して順位を計測します</div>
    </div>
    <div class="form-group">
      <label class="form-label">監視URL（最大50件）</label>
      <div class="url-entry-list" id="urlEntryList"></div>
      <button class="btn-add-url" onclick="addUrlEntry()">＋ URLを追加</button>
      <div class="form-hint">ポジティブ＝自社コンテンツ / ネガティブ＝下落させたいサイト</div>
    </div>
    <div class="modal-actions">
      <button class="btn btn-secondary" onclick="closeKwModal()">キャンセル</button>
      <button class="btn btn-primary" onclick="saveKeyword()">保存</button>
    </div>
  </div>
</div>

<script>
const API_BASE = "https://ranktracker.vibe-shift.jp";
const LOGO_DARK = "https://raw.githubusercontent.com/yukiyvs-hub/reverse-seo-ranktracker/main/assets/images/VS_logo_W_背景無.png";
const LOGO_LIGHT = "https://raw.githubusercontent.com/yukiyvs-hub/reverse-seo-ranktracker/main/assets/images/VS_logo_B_背景無.png";

const NEG_COLORS = ["#ff4a6a","#cc2244","#ff8080","#e6003a","#ff6699","#991133","#ffaaaa","#ff3355"];
const POS_COLORS = ["#6affb8","#6ab8ff","#d46aff","#ffda6a","#6affea","#ff9f6a","#b8ff6a","#ff6af0"];

function getUrlColor(type, negIdx, posIdx) {
  if (type === "negative") return NEG_COLORS[negIdx % NEG_COLORS.length];
  return POS_COLORS[posIdx % POS_COLORS.length];
}
function buildUrlColorMap(urls, urlTypeMap) {
  const colorMap = {};
  let negIdx = 0, posIdx = 0;
  urls.forEach(function(url) {
    const type = urlTypeMap?.[url] || "negative";
    colorMap[url] = getUrlColor(type, negIdx, posIdx);
    if (type === "negative") negIdx++; else posIdx++;
  });
  return colorMap;
}

let currentTheme = localStorage.getItem("theme") || "dark";

function applyTheme(theme) {
  currentTheme = theme;
  document.documentElement.setAttribute("data-theme", theme);
  const label = document.getElementById("themeLabel");
  if (label) label.textContent = theme === "dark" ? "DARK" : "LIGHT";
  const logo = document.getElementById("logoImg");
  if (logo) logo.src = theme === "dark" ? LOGO_DARK : LOGO_LIGHT;
  localStorage.setItem("theme", theme);
}

function toggleTheme() {
  applyTheme(currentTheme === "dark" ? "light" : "dark");
  if (currentKeywordId) {
    const { start, end } = getDateRange();
    const cacheKey = currentKeywordId + "_" + start + "_" + end;
    const data = rankDataCache[cacheKey];
    if (data) {
      const dates = [];
      const d = new Date(start), endD = new Date(end);
      while (d <= endD) { dates.push(d.toISOString().split("T")[0]); d.setDate(d.getDate()+1); }
      const kws = keywordsCache[currentClientId] || [];
      const kw = kws.find(function(k) { return k.keyword_id === currentKeywordId; });
      const urlTypeMap = {};
      if (kw && kw.urls) kw.urls.forEach(function(u) { urlTypeMap[u.url] = u.url_type; });
      const urls = Object.keys(data);
      const urlColorMap = buildUrlColorMap(urls, urlTypeMap);
      drawSvgChart(dates, urls, data, urlTypeMap, urlColorMap);
    }
  }
}

let clients = [], keywordsCache = {}, openClients = new Set();
let currentClientId = null, currentKeywordId = null;
let editingClientId = null, editingKwId = null, editingKwClientId = null;
let periodDays = 30, customStart = null, customEnd = null;
let rankDataCache = {};
let hiddenUrls = new Set();

async function callAPI(endpoint, method, body) {
  method = method || "GET";
  const options = { method: method, headers: { "Content-Type": "application/json" } };
  if (body) options.body = JSON.stringify(body);
  const res = await fetch(API_BASE + "/" + endpoint, options);
  return await res.json();
}

async function init() {
  applyTheme(currentTheme);
  clients = await callAPI("clients.php") || [];
  const allKw = await callAPI("keywords.php") || [];
  allKw.forEach(function(kw) {
    if (!keywordsCache[kw.client_id]) keywordsCache[kw.client_id] = [];
    if (kw.urls) {
      try { kw.urls = typeof kw.urls === "string" ? parseUrls(kw.urls) : kw.urls; } catch(e) { kw.urls = []; }
    }
    keywordsCache[kw.client_id].push(kw);
  });
  renderSidebar();
  const bal = await callAPI("balance.php");
  const el = document.getElementById("balanceBadge");
  if (el && bal && bal.balance !== null) el.textContent = "残高：$" + Number(bal.balance).toFixed(2);
}

function parseUrls(urlString) {
  if (!urlString) return [];
  return urlString.split(",,").map(function(u) {
    const parts = u.split("|");
    return { url: parts[0], url_type: parts[1] || "negative" };
  });
}

async function renderSidebar() {
  const el = document.getElementById("clientList");
  if (!clients.length) { el.innerHTML = "<div style=\"padding:16px;font-size:12px;color:var(--text3);text-align:center\">クライアントがありません</div>"; return; }
  let html = "";
  for (const c of clients) {
    const isOpen = openClients.has(c.client_id);
    html += "<div class=\"client-block\">" +
      "<div class=\"client-row\" onclick=\"toggleClient('" + c.client_id + "')\">" +
        "<span class=\"client-chevron " + (isOpen?"open":"") + "\">▶</span>" +
        "<span class=\"client-name\">" + c.client_name + "</span>" +
        "<div class=\"client-actions\">" +
          "<button class=\"icon-btn\" onclick=\"event.stopPropagation();openClientModal('" + c.client_id + "')\">✏️</button>" +
          "<button class=\"icon-btn danger\" onclick=\"event.stopPropagation();deleteClient('" + c.client_id + "')\">✕</button>" +
        "</div>" +
      "</div>" +
      (isOpen ? renderKeywordList(c.client_id) : "") +
    "</div>";
  }
  el.innerHTML = html;
}

function renderKeywordList(clientId) {
  const kws = keywordsCache[clientId] || [];
  let html = "<div class=\"keyword-list\">";
  kws.forEach(function(kw) {
    html += "<div class=\"keyword-row " + (kw.keyword_id===currentKeywordId?"active":"") + "\" onclick=\"window.selectKeyword('" + kw.keyword_id + "','" + clientId + "')\">" +
      "<span class=\"keyword-text\">" + kw.keyword + "</span>" +
      "<div class=\"keyword-actions\">" +
        "<button class=\"icon-btn\" onclick=\"event.stopPropagation();openKwModal('" + clientId + "','" + kw.keyword_id + "')\">✏️</button>" +
        "<button class=\"icon-btn danger\" onclick=\"event.stopPropagation();deleteKeyword('" + kw.keyword_id + "','" + clientId + "')\">✕</button>" +
      "</div>" +
    "</div>";
  });
  html += "<div class=\"kw-add-btn\" onclick=\"openKwModal('" + clientId + "',null)\"><span>＋ キーワード追加</span></div></div>";
  return html;
}

async function toggleClient(clientId) {
  if (openClients.has(clientId)) { openClients.delete(clientId); } else {
    openClients.add(clientId);
    if (!keywordsCache[clientId]) {
      const res = await callAPI("keywords.php?client_id=" + clientId);
      keywordsCache[clientId] = (res || []).map(function(kw) {
        if (kw.urls && typeof kw.urls === "string") kw.urls = parseUrls(kw.urls);
        return kw;
      });
    }
  }
  currentClientId = clientId;
  await renderSidebar();
}

window.selectKeyword = async function(keywordId, clientId) {
  currentKeywordId = keywordId;
  currentClientId = clientId;
  hiddenUrls = new Set();
  await renderDashboard();
  await renderSidebar();
}

async function renderDashboard() {
  const client = clients.find(function(c) { return c.client_id === currentClientId; });
  const kws = keywordsCache[currentClientId] || [];
  const kw = kws.find(function(k) { return k.keyword_id === currentKeywordId; });
  if (!client || !kw) return;
  const pd7 = periodDays===7?"active":"";
  const pd30 = periodDays===30?"active":"";
  const pd90 = periodDays===90?"active":"";
  const pd0 = periodDays===0?"active":"";
  const cdDisplay = periodDays===0?"flex":"none";
  document.getElementById("main").innerHTML =
    "<div class=\"dashboard\">" +
      "<div class=\"dash-header\">" +
        "<div>" +
          "<div class=\"dash-client\">" + client.client_name + "</div>" +
          "<div class=\"dash-title\">" + kw.keyword + "</div>" +
        "</div>" +
        "<div class=\"dash-actions\">" +
          "<button class=\"btn btn-secondary\" onclick=\"openKwModal('" + client.client_id + "','" + kw.keyword_id + "')\">✏️ 編集</button>" +
          "<button class=\"btn btn-primary\" id=\"measureBtn\" onclick=\"measureNow()\">▶ 今すぐ計測</button>" +
        "</div>" +
      "</div>" +
      "<div class=\"period-bar\">" +
        "<span class=\"period-label\">期間：</span>" +
        "<button class=\"period-btn " + pd7 + "\" onclick=\"setPeriod(7)\">7日</button>" +
        "<button class=\"period-btn " + pd30 + "\" onclick=\"setPeriod(30)\">30日</button>" +
        "<button class=\"period-btn " + pd90 + "\" onclick=\"setPeriod(90)\">90日</button>" +
        "<button class=\"period-btn " + pd0 + "\" onclick=\"setPeriod(0)\">カスタム</button>" +
        "<span id=\"customDateRange\" style=\"display:" + cdDisplay + ";gap:6px;align-items:center\">" +
          "<input type=\"date\" class=\"date-input\" id=\"dateStart\" value=\"" + (customStart||"") + "\" onchange=\"customStart=this.value;loadChartData()\">" +
          "<span style=\"color:var(--text3)\">〜</span>" +
          "<input type=\"date\" class=\"date-input\" id=\"dateEnd\" value=\"" + (customEnd||"") + "\" onchange=\"customEnd=this.value;loadChartData()\">" +
        "</span>" +
      "</div>" +
      "<div class=\"chart-wrap\">" +
        "<div class=\"chart-title\">順位推移</div>" +
        "<div class=\"chart-svg-wrap\" id=\"chartSvgWrap\"></div>" +
        "<div class=\"chart-legend\" id=\"chartLegend\"></div>" +
      "</div>" +
      "<div class=\"rank-table-wrap\">" +
        "<div class=\"rank-table-head\"><div class=\"rank-table-title\">監視URL一覧（最新順位）</div></div>" +
        "<div id=\"rankTable\"><table><tr><td style=\"padding:20px;color:var(--text3);text-align:center\">読み込み中...</td></tr></table></div>" +
      "</div>" +
    "</div>";
  await loadChartData();
}

function setPeriod(days) { periodDays = days; renderDashboard(); }

function getDateRange() {
  if (periodDays === 0) return { start: customStart, end: customEnd };
  const now = new Date();
  const jst = new Date(now.getTime() + 9 * 60 * 60 * 1000);
  const end = jst.toISOString().split("T")[0];
  const startD = new Date(jst.getTime() - periodDays * 24 * 60 * 60 * 1000);
  const start = startD.toISOString().split("T")[0];
  return { start: start, end: end };
}

async function loadChartData() {
  const range = getDateRange();
  const start = range.start, end = range.end;
  if (!start || !end) return;
  const cacheKey = currentKeywordId + "_" + start + "_" + end;
  let data = rankDataCache[cacheKey];
  if (!data) {
    const logs = await callAPI("rank_logs.php?keyword_id=" + currentKeywordId + "&limit=500");
    data = {};
    (logs || []).forEach(function(log) {
      if (!data[log.url]) data[log.url] = {};
      const date = log.measured_at.split(" ")[0];
      if (date >= start && date <= end) data[log.url][date] = log.rank || "圏外";
    });
    rankDataCache[cacheKey] = data;
  }
  const dates = [];
  const d = new Date(start), endD = new Date(end);
  while (d <= endD) { dates.push(d.toISOString().split("T")[0]); d.setDate(d.getDate()+1); }
  const kws = keywordsCache[currentClientId] || [];
  const kw = kws.find(function(k) { return k.keyword_id === currentKeywordId; });
  const urlTypeMap = {};
  if (kw && kw.urls) kw.urls.forEach(function(u) { urlTypeMap[u.url] = u.url_type; });
  const urls = Object.keys(data);
  const urlColorMap = buildUrlColorMap(urls, urlTypeMap);
  drawSvgChart(dates, urls, data, urlTypeMap, urlColorMap);
  renderRankTable(urls, data, dates, urlTypeMap, urlColorMap);
}

function getChartColors() {
  return currentTheme === "dark"
    ? { grid: "#2a2a38", label: "#555568" }
    : { grid: "#dddde8", label: "#9a9ab8" };
}

function drawSvgChart(dates, urls, data, urlTypeMap, urlColorMap) {
  const wrap = document.getElementById("chartSvgWrap");
  const W = wrap.clientWidth || 600;
  const H = 300;
  const PAD = { top: 20, right: 20, bottom: 40, left: 40 };
  const innerW = W - PAD.left - PAD.right;
  const minRank = 1, maxRank = 50;
  const colors = getChartColors();
  const xScale = function(i) { return PAD.left + (i / Math.max(dates.length - 1, 1)) * innerW; };
  const yScale = function(r) { return PAD.top + ((r - minRank) / (maxRank - minRank)) * (H - PAD.top - PAD.bottom); };

  let svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"100%\" height=\"" + H + "\" viewBox=\"0 0 " + W + " " + H + "\" style=\"font-family:DM Mono,monospace\">";

  [1,10,20,30,40,50].forEach(function(r) {
    const y = yScale(r);
    svg += "<line x1=\"" + PAD.left + "\" y1=\"" + y + "\" x2=\"" + (W-PAD.right) + "\" y2=\"" + y + "\" stroke=\"" + colors.grid + "\" stroke-width=\"1\"/>";
    svg += "<text x=\"" + (PAD.left-6) + "\" y=\"" + (y+4) + "\" text-anchor=\"end\" font-size=\"10\" fill=\"" + colors.label + "\">" + r + "</text>";
  });

  const step = Math.ceil(dates.length / 10);
  dates.forEach(function(dt, i) {
    if (i % step !== 0 && i !== dates.length - 1) return;
    svg += "<text x=\"" + xScale(i) + "\" y=\"" + (H-PAD.bottom+16) + "\" text-anchor=\"middle\" font-size=\"10\" fill=\"" + colors.label + "\">" + dt.slice(5) + "</text>";
  });

  urls.forEach(function(url) {
    if (hiddenUrls.has(url)) return;
    const color = urlColorMap[url];
    const dates2 = data[url];
    const points = dates.map(function(dt, i) {
      const r = (typeof dates2 === "object" && !Array.isArray(dates2)) ? dates2[dt] : null;
      if (r === undefined || r === null) return null;
      if (r === "圏外") return { x: xScale(i), y: yScale(50), r: "50位以下" };
      return { x: xScale(i), y: yScale(Number(r)), r: Number(r) };
    });
    let pathD = "";
    points.forEach(function(p, i) {
      if (!p) return;
      const prev = points.slice(0, i).reverse().find(function(pp) { return pp; });
      if (!prev) pathD += "M " + p.x + " " + p.y;
      else pathD += " L " + p.x + " " + p.y;
    });
    if (pathD) svg += "<path d=\"" + pathD + "\" fill=\"none\" stroke=\"" + color + "\" stroke-width=\"2\" stroke-linejoin=\"round\"/>";
    points.forEach(function(p) {
      if (!p) return;
      const tipText = url + ": " + (p.r === "50位以下" ? "50位以下" : p.r + "位");
      svg += "<circle cx=\"" + p.x + "\" cy=\"" + p.y + "\" r=\"4\" fill=\"" + color + "\" onmouseenter=\"showTip(event,'" + tipText + "')\" onmouseleave=\"hideTip()\" style=\"cursor:pointer\"/>";
    });
  });

  svg += "</svg>";
  wrap.innerHTML = svg;

  document.getElementById("chartLegend").innerHTML = urls.map(function(url) {
    const type = (urlTypeMap && urlTypeMap[url]) ? urlTypeMap[url] : "negative";
    const color = urlColorMap[url];
    const lbl = type === "positive" ? "POS" : "NEG";
    const isHidden = hiddenUrls.has(url);
    return "<div class=\"legend-item " + (isHidden?"hidden":"") + "\" onclick=\"toggleUrl('" + url + "')\" title=\"クリックで表示/非表示\">" +
      "<div class=\"legend-dot\" style=\"background:" + color + "\"></div>" +
      "<span class=\"type-badge " + type + "\">" + lbl + "</span>" +
      "<span>" + url + "</span>" +
    "</div>";
  }).join("");
}

function toggleUrl(url) {
  if (hiddenUrls.has(url)) hiddenUrls.delete(url); else hiddenUrls.add(url);
  const range = getDateRange();
  const cacheKey = currentKeywordId + "_" + range.start + "_" + range.end;
  const data = rankDataCache[cacheKey];
  if (!data) return;
  const dates = [];
  const d = new Date(range.start), endD = new Date(range.end);
  while (d <= endD) { dates.push(d.toISOString().split("T")[0]); d.setDate(d.getDate()+1); }
  const kws = keywordsCache[currentClientId] || [];
  const kw = kws.find(function(k) { return k.keyword_id === currentKeywordId; });
  const urlTypeMap = {};
  if (kw && kw.urls) kw.urls.forEach(function(u) { urlTypeMap[u.url] = u.url_type; });
  const urls = Object.keys(data);
  drawSvgChart(dates, urls, data, urlTypeMap, buildUrlColorMap(urls, urlTypeMap));
}

function renderRankTable(urls, data, dates, urlTypeMap, urlColorMap) {
  const kws = keywordsCache[currentClientId] || [];
  const kw = kws.find(function(k) { return k.keyword_id === currentKeywordId; });
  const latestDate = dates[dates.length - 1];
  const allUrls = (kw && kw.urls && kw.urls.length) ? kw.urls.map(function(u) { return u.url; }) : urls;
  const rows = allUrls.map(function(url) {
    const urlData = data[url] || {};
    const type = (urlTypeMap && urlTypeMap[url]) ? urlTypeMap[url] : "negative";
    const rank = urlData[latestDate];
    const color = (urlColorMap && urlColorMap[url]) ? urlColorMap[url] : (type === "positive" ? "#6affb8" : "#ff6a8a");
    let rankClass = "rank-out", rankDisplay = "50位以下";
    if (rank !== undefined && rank !== null && rank !== "圏外") {
      const n = Number(rank);
      rankDisplay = n + "位";
      rankClass = n <= 10 ? "rank-1-10" : n <= 30 ? "rank-11-30" : "rank-31-50";
    }
    const badge = "<span class=\"type-badge " + type + "\">" + (type === "positive" ? "POS" : "NEG") + "</span>";
    return "<tr><td><span class=\"rank-num " + rankClass + "\">" + rankDisplay + "</span></td><td>" + badge + "</td><td><div style=\"display:flex;align-items:center;gap:8px\"><div style=\"width:4px;height:32px;border-radius:2px;background:" + color + ";flex-shrink:0\"></div><span class=\"url-text\">" + url + "</span></div></td></tr>";
  }).join("");
  document.getElementById("rankTable").innerHTML =
    "<table><thead><tr><th style=\"width:110px\">最新順位</th><th style=\"width:60px\">種別</th><th>URL</th></tr></thead><tbody>" +
    (rows || "<tr><td colspan=\"3\" style=\"text-align:center;color:var(--text3);padding:20px\">データなし</td></tr>") +
    "</tbody></table>";
  document.getElementById("lastUpdated").textContent = "最終更新：" + (latestDate || "-");
}

async function measureNow() {
  const btn = document.getElementById("measureBtn");
  btn.disabled = true; btn.innerHTML = "<span class=\"loading\"></span> 計測中...";
  try {
    const result = await callAPI("measure_post.php", "POST", { keywordId: currentKeywordId });

    if (result.error) throw new Error(result.error);
    rankDataCache = {};
    showToast("計測タスクを送信しました！数分後に結果が反映されます", "success");
    await loadChartData();
  } catch(e) { showToast("エラー：" + e.message, "error"); }
  finally { btn.disabled = false; btn.innerHTML = "▶ 今すぐ計測"; }
}

function openClientModal(clientId) {
  editingClientId = clientId;
  const c = clients.find(function(c) { return c.client_id === clientId; });
  document.getElementById("clientModalTitle").textContent = c ? "クライアント編集" : "クライアント追加";
  document.getElementById("clientName").value = c ? c.client_name : "";
  document.getElementById("clientModal").style.display = "flex";
}
function closeClientModal() { document.getElementById("clientModal").style.display = "none"; }

async function saveClient() {
  const name = document.getElementById("clientName").value.trim();
  if (!name) return showToast("クライアント名を入力してください", "error");
  const method = editingClientId ? "PUT" : "POST";
  const body = editingClientId ? { client_id: editingClientId, client_name: name } : { client_name: name };
  const result = await callAPI("clients.php", method, body);
  if (result.error) return showToast(result.error, "error");
  closeClientModal();
  clients = await callAPI("clients.php") || [];
  await renderSidebar();
  showToast("保存しました", "success");
}

async function deleteClient(clientId) {
  if (!confirm("このクライアントを削除しますか？")) return;
  await callAPI("clients.php", "DELETE", { client_id: clientId });
  clients = await callAPI("clients.php") || [];
  delete keywordsCache[clientId]; openClients.delete(clientId);
  if (currentClientId === clientId) { currentClientId = null; currentKeywordId = null; }
  await renderSidebar();
  if (!currentKeywordId) document.getElementById("main").innerHTML = "<div class=\"empty-state\"><div class=\"empty-icon\">📊</div><div style=\"font-size:14px\">クライアントを選択してください</div></div>";
}

function addUrlEntry(url, type) {
  url = url || "";
  type = type || "negative";
  const list = document.getElementById("urlEntryList");
  const div = document.createElement("div");
  div.className = "url-entry";
  const input = document.createElement("input");
  input.type = "text";
  input.placeholder = "https://example.com/article";
  input.value = url;
  const select = document.createElement("select");
  const optNeg = document.createElement("option");
  optNeg.value = "negative";
  optNeg.textContent = "ネガティブ";
  optNeg.selected = (type === "negative");
  const optPos = document.createElement("option");
  optPos.value = "positive";
  optPos.textContent = "ポジティブ";
  optPos.selected = (type === "positive");
  select.appendChild(optNeg);
  select.appendChild(optPos);
  const btn = document.createElement("button");
  btn.className = "remove-btn";
  btn.textContent = "✕";
  btn.onclick = function() { div.remove(); };
  div.appendChild(input);
  div.appendChild(select);
  div.appendChild(btn);
  list.appendChild(div);
}

function openKwModal(clientId, kwId) {
  editingKwClientId = clientId; editingKwId = kwId;
  const kws = keywordsCache[clientId] || [];
  const kw = kws.find(function(k) { return k.keyword_id === kwId; });
  document.getElementById("kwModalTitle").textContent = kw ? "キーワード編集" : "キーワード追加";
  document.getElementById("kwText").value = kw ? kw.keyword : "";
  document.getElementById("urlEntryList").innerHTML = "";
  if (kw && kw.urls && kw.urls.length) {
    kw.urls.forEach(function(u) { addUrlEntry(u.url, u.url_type); });
  } else {
    addUrlEntry();
  }
  document.getElementById("kwModal").style.display = "flex";
}
function closeKwModal() { document.getElementById("kwModal").style.display = "none"; }

async function saveKeyword() {
  const keyword = document.getElementById("kwText").value.trim();
  if (!keyword) return showToast("キーワードを入力してください", "error");
  const entries = document.querySelectorAll(".url-entry");
  const urls = [];
  entries.forEach(function(entry) {
    const url = entry.querySelector("input[type='text']").value.trim();
    const type = entry.querySelector("select").value;
    if (url) urls.push({ url: url, url_type: type });
  });
  const method = editingKwId ? "PUT" : "POST";
  const body = { keyword_id: editingKwId, client_id: editingKwClientId, keyword: keyword, urls: urls };
  const result = await callAPI("keywords.php", method, body);
  if (result.error) return showToast(result.error, "error");
  closeKwModal();
  keywordsCache[editingKwClientId] = null;
  rankDataCache = {};
  const res = await callAPI("keywords.php?client_id=" + editingKwClientId);
  keywordsCache[editingKwClientId] = (res || []).map(function(kw) {
    if (kw.urls && typeof kw.urls === "string") kw.urls = parseUrls(kw.urls);
    return kw;
  });
  await renderSidebar();
  if (currentKeywordId === editingKwId) await renderDashboard();
  showToast("保存しました", "success");
}

async function deleteKeyword(kwId, clientId) {
  if (!confirm("このキーワードを削除しますか？")) return;
  await callAPI("keywords.php", "DELETE", { keyword_id: kwId });
  keywordsCache[clientId] = null;
  if (currentKeywordId === kwId) { currentKeywordId = null; document.getElementById("main").innerHTML = "<div class=\"empty-state\"><div class=\"empty-icon\">📊</div><div style=\"font-size:14px\">キーワードを選択してください</div></div>"; }
  await renderSidebar();
}

function showToast(msg, type) {
  type = type || "success";
  const t = document.createElement("div");
  t.className = "toast " + type;
  t.textContent = (type === "success" ? "✓ " : "✕ ") + msg;
  document.body.appendChild(t);
  setTimeout(function() { t.remove(); }, 3000);
}

function showTip(evt, text) {
  let tip = document.getElementById("svgTooltip");
  if (!tip) {
    tip = document.createElement("div");
    tip.id = "svgTooltip";
    tip.style.cssText = "position:fixed;background:var(--bg2);border:1px solid var(--border);border-radius:6px;padding:6px 10px;font-size:12px;font-family:var(--mono);color:var(--text);pointer-events:none;z-index:999;box-shadow:0 2px 8px var(--shadow);max-width:300px;word-break:break-all;";
    document.body.appendChild(tip);
  }
  tip.textContent = text;
  tip.style.display = "block";
  const tipW = tip.offsetWidth, tipH = tip.offsetHeight;
  const winW = window.innerWidth, winH = window.innerHeight;
  let left = evt.clientX + 12;
  if (left + tipW > winW - 10) left = evt.clientX - tipW - 12;
  let top = evt.clientY - 28;
  if (top < 10) top = evt.clientY + 12;
  if (top + tipH > winH - 10) top = evt.clientY - tipH - 12;
  tip.style.left = left + "px";
  tip.style.top = top + "px";
}

function hideTip() {
  const tip = document.getElementById("svgTooltip");
  if (tip) tip.style.display = "none";
}

init();
</script>
</body>
</html>