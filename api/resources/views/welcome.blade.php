<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>IoT Dashboard Builder</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gridstack@10.2.0/dist/gridstack.min.css" />
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}" />
  <script src="https://cdn.jsdelivr.net/npm/gridstack@10.2.0/dist/gridstack-all.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="app">
  <div class="toolbar">
    <div class="left">
      <h1 class="title">Dashboard Banjir</h1>
      <span class="status" id="saveStatus">Layout belum dimuat...</span>
    </div>
    <div class="right">
      <button class="btn secondary icon-only" id="themeBtn" title="Mode Gelap/Terang" aria-label="Mode Gelap/Terang">
        <svg class="theme-icon" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"></path>
        </svg>
      </button>
      <button class="btn warning icon-only" id="sirenBtn" title="Nyalakan/Matikan Audio Sirene" aria-label="Nyalakan/Matikan Audio Sirene">
        <svg class="siren-icon" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M8 14a4 4 0 0 1 8 0v3H8z"></path>
          <path d="M7 17h10"></path>
          <path d="M12 4v2"></path>
          <path d="M5 8l1.5 1"></path>
          <path d="M19 8l-1.5 1"></path>
        </svg>
      </button>
      <button class="btn secondary" id="sirenModeBtn" title="Ubah mode sirine">Mode Sirine: Manual</button>
      <input id="sirenFileInput" type="file" accept="audio/mpeg,.mp3" style="display:none" />
      <button class="btn secondary icon-only" id="lockBtn" title="Kunci/Buka Kunci Layout" aria-label="Kunci/Buka Kunci Layout">
        <svg class="lock-icon lock-open" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M17 8h-1V6a4 4 0 0 0-7.87-1"/>
          <rect x="5" y="8" width="14" height="12" rx="2" ry="2"/>
        </svg>
      </button>
      <button class="btn success" id="addWidgetBtn">Tambah Widget</button>
      <button class="btn danger" id="resetBtn">Reset Layout</button>
    </div>
  </div>
  <div class="grid-stack" id="dashboardGrid"></div>
</div>

<div class="modal" id="widgetModal">
  <div class="modal-card">
    <h3 id="modalTitle">Tambah Widget</h3>
    <div class="field-grid">
      <div class="field">
        <label for="widgetType">Tipe Widget</label>
        <select id="widgetType">
          <option value="level">Level (Klasifikasi)</option>
          <option value="chart_device">Grafik per Perangkat</option>
          <option value="device_status">Status Perangkat</option>
          <option value="control_panel">Panel Kontrol</option>
          <option value="water_history">Riwayat Ketinggian Air</option>
          <option value="command_history">Riwayat Command</option>
          <option value="worker_status">Status Worker</option>
          <option value="stat_online">Device Online</option>
          <option value="stat_avg">Rata-rata Ketinggian</option>
          <option value="stat_max">Status Tertinggi</option>
          <option value="stat_total">Total Data</option>
        </select>
      </div>
      <div class="field">
        <label for="widgetTitle">Judul Widget</label>
        <input id="widgetTitle" type="text" placeholder="Contoh: Tinggi Air" />
      </div>
    </div>
    <div class="field-grid">
      <div class="field">
        <label for="widgetDevice">Perangkat</label>
        <select id="widgetDevice"><option value="">Semua device</option></select>
      </div>
      <div class="field">
        <label for="widgetField">Data Sensor</label>
        <select id="widgetField">
          <option value="water_level">Tinggi Air (cm)</option>
        </select>
      </div>
    </div>
    <div class="field-grid">
      <div class="field">
        <label for="widgetUnit">Unit</label>
        <input id="widgetUnit" type="text" placeholder="Contoh: cm" />
      </div>
      <div class="field" id="fieldChartColor">
        <label for="widgetChartColor">Warna Grafik</label>
        <input id="widgetChartColor" type="color" value="#ef4444" />
      </div>
    </div>
    <div class="field-grid" id="gaugeConfigGroup">
      <div class="field"><label for="widgetGaugeMin">Nilai Minimum</label><input id="widgetGaugeMin" type="number" value="0" /></div>
      <div class="field"><label for="widgetGaugeMax">Nilai Maksimum</label><input id="widgetGaugeMax" type="number" value="100" /></div>
    </div>
    <div class="field-grid" id="controlConfigGroup">
      <div class="field"><label for="widgetCmdOn">Perintah Aktifkan</label><input id="widgetCmdOn" type="text" value="start" /></div>
      <div class="field"><label for="widgetCmdOff">Perintah Nonaktifkan</label><input id="widgetCmdOff" type="text" value="stop" /></div>
      <div class="field"><label for="widgetCmdAlert">Perintah Sirine</label><input id="widgetCmdAlert" type="text" value="alert" /></div>
      <div class="field"><label for="widgetCmdReset">Perintah Reset</label><input id="widgetCmdReset" type="text" value="reset" /></div>
    </div>

    <div class="field-grid" id="chartConfigGroup">
      <div class="field"><label for="widgetChartPoints">Jumlah Titik Data</label><input id="widgetChartPoints" type="number" value="60" min="10" max="300" /></div>
      <div class="field"><label for="widgetChartMode">Tampilkan</label>
        <select id="widgetChartMode">
          <option value="single">Satu perangkat (pilih)</option>
          <option value="multi">Multi perangkat</option>
        </select>
      </div>
    </div>

    <div class="field-grid" id="thresholdConfigGroup">
      <div class="field"><label for="thAmanMax">Normal — air &lt; (cm)</label><input id="thAmanMax" type="number" value="101" min="1" step="1" /></div>
      <div class="field"><label for="thSiagaMax">Siaga — hingga (cm)</label><input id="thSiagaMax" type="number" value="200" min="1" step="1" /></div>
      <div class="field"><label>Awas</label><input type="text" value="&gt; batas siaga (cm)" disabled /></div>
    </div>

    <div class="field-grid" id="mapConfigGroup" style="display:none"></div>
    <div class="modal-actions">
      <button class="btn secondary" id="cancelWidgetBtn">Batal</button>
      <button class="btn success" id="saveWidgetBtn">Simpan</button>
    </div>
  </div>
</div>

<script>
  const API_BASE = "/api";
  const API_HEADERS = { "X-API-KEY": "FLOOD-SECRET-KEY-2025", "Content-Type": "application/json" };

  function generateId() { return (window.crypto && window.crypto.randomUUID) ? window.crypto.randomUUID() : `w-${Date.now()}-${Math.random().toString(16).slice(2)}`; }
  function defaultWidget(overrides = {}) {
    return { id: generateId(), type: "metric", title: "Widget", field: "water_level", unit: "", device_id: "", chartColor: "#ef4444", gaugeMin: 0, gaugeMax: 100, commandOn: "start", commandOff: "stop", value: 57, state: false, x: 0, y: 0, w: 3, h: 3, ...overrides };
  }

  const DEFAULT_LAYOUT = [];

  const appState = { grid: null, widgets: [], charts: new Map(), sensorData: [], devices: [], logs: [], commands: [], workerStatuses: [], locked: false, saveTimer: null, editId: null };

  const elGrid = document.getElementById("dashboardGrid");
  const elSaveStatus = document.getElementById("saveStatus");
  const elThemeBtn = document.getElementById("themeBtn");
  const elSirenBtn = document.getElementById("sirenBtn");
  const elSirenModeBtn = document.getElementById("sirenModeBtn");
  const elSirenFileInput = document.getElementById("sirenFileInput");
  const elLockBtn = document.getElementById("lockBtn");
  const elWidgetModal = document.getElementById("widgetModal");
  const elModalTitle = document.getElementById("modalTitle");
  const elWidgetType = document.getElementById("widgetType");
  const elWidgetTitle = document.getElementById("widgetTitle");
  const elWidgetDevice = document.getElementById("widgetDevice");
  const elWidgetField = document.getElementById("widgetField");
  const elWidgetUnit = document.getElementById("widgetUnit");
  const elWidgetChartColor = document.getElementById("widgetChartColor");
  const elWidgetGaugeMin = document.getElementById("widgetGaugeMin");
  const elWidgetGaugeMax = document.getElementById("widgetGaugeMax");
  const elWidgetCmdOn = document.getElementById("widgetCmdOn");
  const elWidgetCmdOff = document.getElementById("widgetCmdOff");
  const elChartColorField = document.getElementById("fieldChartColor");
  const elGaugeConfigGroup = document.getElementById("gaugeConfigGroup");
  const elToggleConfigGroup = document.getElementById("toggleConfigGroup");
  const elChartConfigGroup = document.getElementById("chartConfigGroup");
  const elWidgetChartPoints = document.getElementById("widgetChartPoints");
  const elWidgetChartMode = document.getElementById("widgetChartMode");
  const elThresholdConfigGroup = document.getElementById("thresholdConfigGroup");
  const elThAmanMax = document.getElementById("thAmanMax");
  const elThSiagaMax = document.getElementById("thSiagaMax");
  const elMapConfigGroup = document.getElementById("mapConfigGroup");
  const elWidgetCmdAlert = document.getElementById("widgetCmdAlert");
  const elWidgetCmdReset = document.getElementById("widgetCmdReset");
  const elControlConfigGroup = document.getElementById("controlConfigGroup");
  let sirenAudio = null;
  let sirenAudioUrl = "";
  let sirenFileName = "";
  let sirenAutoMode = false;
  let sirenManualMuteUntilMs = 0;
  let sirenToneCtx = null;
  let sirenToneOsc = null;
  let sirenToneLfo = null;
  let sirenToneLfoGain = null;
  let sirenToneGain = null;
  let sirenAudioUnlocked = false;

  function setStatus(message) { elSaveStatus.textContent = message; }
  function isSirenActive() {
    const mp3On = !!sirenAudio && !sirenAudio.paused;
    const toneOn = !!sirenToneOsc;
    return mp3On || toneOn;
  }
  async function unlockSirenAudio() {
    try {
      if (!sirenToneCtx) sirenToneCtx = new (window.AudioContext || window.webkitAudioContext)();
      if (sirenToneCtx.state === "suspended") await sirenToneCtx.resume();
      sirenAudioUnlocked = true;
      return true;
    } catch (error) {
      console.error(error);
      return false;
    }
  }
  function isDeviceOnline(deviceId) {
    if (!deviceId) return false;
    const device = appState.devices.find((d) => d.device_id === deviceId);
    return !!device && device.status === "online";
  }
  function updateSirenBtn() {
    if (!elSirenBtn) return;
    const isOn = isSirenActive();
    elSirenBtn.classList.toggle("active", isOn);
    if (!sirenAudioUrl) {
      elSirenBtn.title = "Pilih audio sirene (.mp3)";
      elSirenBtn.setAttribute("aria-label", "Pilih audio sirene");
      return;
    }
    const base = sirenFileName ? `Audio: ${sirenFileName}` : "Audio sirene siap";
    const state = isOn ? "ON" : "OFF";
    elSirenBtn.title = `${base} (${state})`;
    elSirenBtn.setAttribute("aria-label", `${base} ${state}`);
    refreshSirenControlButtons();
  }
  function refreshSirenControlButtons() {
    const isOn = isSirenActive();
    const buttons = document.querySelectorAll("[data-action='control'][data-cmd='alert']");
    buttons.forEach((btn) => {
      btn.textContent = isOn ? "Sirine ON" : "Sirine OFF";
    });
  }
  async function startFallbackSirenTone() {
    try {
      const unlocked = await unlockSirenAudio();
      if (!unlocked || !sirenToneCtx) {
        setStatus("Klik sekali layar untuk mengizinkan audio otomatis.");
        return false;
      }
      if (sirenToneOsc) return true;

      sirenToneOsc = sirenToneCtx.createOscillator();
      sirenToneLfo = sirenToneCtx.createOscillator();
      sirenToneLfoGain = sirenToneCtx.createGain();
      sirenToneGain = sirenToneCtx.createGain();

      sirenToneOsc.type = "sawtooth";
      sirenToneOsc.frequency.value = 680;
      sirenToneLfo.type = "sine";
      sirenToneLfo.frequency.value = 0.9;
      sirenToneLfoGain.gain.value = 220;
      sirenToneGain.gain.value = 0.05;

      sirenToneLfo.connect(sirenToneLfoGain);
      sirenToneLfoGain.connect(sirenToneOsc.frequency);
      sirenToneOsc.connect(sirenToneGain);
      sirenToneGain.connect(sirenToneCtx.destination);

      sirenToneOsc.start();
      sirenToneLfo.start();
      updateSirenBtn();
      return true;
    } catch (error) {
      console.error(error);
      return false;
    }
  }
  function updateSirenModeBtn() {
    if (!elSirenModeBtn) return;
    elSirenModeBtn.textContent = sirenAutoMode ? "Mode Sirine: Otomatis" : "Mode Sirine: Manual";
    elSirenModeBtn.classList.toggle("active", sirenAutoMode);
  }
  function setSirenMode(enabled) {
    sirenAutoMode = Boolean(enabled);
    localStorage.setItem("siren-auto-mode", sirenAutoMode ? "1" : "0");
    if (!sirenAutoMode) {
      sirenManualMuteUntilMs = 0;
    }
    updateSirenModeBtn();
    if (sirenAutoMode) {
      evaluateAutoSiren();
    }
  }
  function toggleSirenMode() {
    setSirenMode(!sirenAutoMode);
    setStatus(sirenAutoMode ? "Mode sirine otomatis aktif" : "Mode sirine manual aktif");
  }
  function getAutoSirenTrigger() {
    for (const row of appState.sensorData) {
      const water = Number(row?.water_level ?? 0);
      const level = getFloodLevel(water);
      const backendAlert = String(row?.alert_level || "").toLowerCase();
      const byFrontendLevel = level.label === "AWAS";
      const byBackendAlert = backendAlert === "danger";
      if ((byFrontendLevel || byBackendAlert) && isDeviceOnline(row.device_id)) {
        return { row, level };
      }
    }
    return null;
  }
  async function evaluateAutoSiren() {
    if (!sirenAutoMode) return;
    const trigger = getAutoSirenTrigger();
    const sirenIsOn = isSirenActive();
    const nowMs = Date.now();

    if (!trigger) {
      // Reset snooze saat kondisi sudah aman.
      if (sirenManualMuteUntilMs > 0) sirenManualMuteUntilMs = 0;
      if (sirenIsOn) {
        stopSirenAudio();
        setStatus("Auto sirine OFF (kondisi sudah aman)");
      }
      return;
    }

    if (sirenManualMuteUntilMs > nowMs) return;
    if (sirenIsOn) return;

    const ok = await playSirenAudio();
    if (ok) {
      setStatus(`Auto sirine ON: ${trigger.row.device_id} (${trigger.level.label})`);
    }
  }
  function setSirenAudioSource(dataUrl, fileName = "siren.mp3") {
    if (!dataUrl) return;
    if (sirenAudioUrl && sirenAudioUrl.startsWith("blob:")) URL.revokeObjectURL(sirenAudioUrl);
    sirenAudioUrl = dataUrl;
    sirenFileName = fileName || "siren.mp3";
    sirenAudio = new Audio(sirenAudioUrl);
    sirenAudio.loop = true;
    sirenAudio.preload = "auto";
    sirenAudio.addEventListener("ended", updateSirenBtn);
    sirenAudio.addEventListener("pause", updateSirenBtn);
    sirenAudio.addEventListener("play", updateSirenBtn);
    updateSirenBtn();
  }
  function readFileAsDataUrl(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => resolve(String(reader.result || ""));
      reader.onerror = reject;
      reader.readAsDataURL(file);
    });
  }
  async function uploadSirenAudio(file) {
    const dataUrl = await readFileAsDataUrl(file);
    const commaIdx = dataUrl.indexOf(",");
    const header = commaIdx >= 0 ? dataUrl.slice(0, commaIdx) : "";
    const base64Body = commaIdx >= 0 ? dataUrl.slice(commaIdx + 1) : "";
    const mimeFromDataUrl = (header.match(/^data:([^;]+);base64$/i) || [])[1] || "audio/mpeg";

    const response = await fetch(`${API_BASE}/dashboard/siren-audio`, {
      method: "POST",
      headers: API_HEADERS,
      body: JSON.stringify({
        file_name: file.name || "siren.mp3",
        mime_type: mimeFromDataUrl,
        audio_base64: base64Body
      })
    });
    const json = await response.json().catch(() => ({}));
    if (!response.ok) {
      const validationMsg = json?.errors?.audio?.[0];
      const msg = validationMsg || json?.error || `HTTP ${response.status}`;
      throw new Error(msg);
    }
    return json;
  }
  async function loadSirenAudioFromServer() {
    try {
      const response = await fetch(`${API_BASE}/dashboard/siren-audio`, { headers: API_HEADERS });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const json = await response.json();
      const audio = json?.audio;
      if (audio?.data_url) {
        setSirenAudioSource(audio.data_url, audio.file_name || "siren.mp3");
        return audio;
      } else {
        updateSirenBtn();
        return null;
      }
    } catch (error) {
      console.error(error);
      updateSirenBtn();
      return null;
    }
  }
  function handleSirenFileSelected(event) {
    const file = event.target.files?.[0];
    if (!file) return;
    const isMp3ByName = /\.mp3$/i.test(file.name || "");
    if (!isMp3ByName) {
      setStatus("Format audio harus MP3");
      event.target.value = "";
      return;
    }
    uploadSirenAudio(file)
      .then(async (json) => {
        let audio = json?.audio;
        if (!audio?.data_url) {
          audio = await loadSirenAudioFromServer();
        }
        if (audio?.data_url) {
          setSirenAudioSource(audio.data_url, audio.file_name || file.name);
          setStatus(`Audio sirene disimpan: ${audio.file_name || file.name}`);
          return;
        }

        // Fallback: tetap pakai file lokal agar fitur jalan meski response server belum lengkap.
        setSirenAudioSource(URL.createObjectURL(file), file.name);
        setStatus("Audio dipakai lokal. Simpan server belum tervalidasi, coba refresh.");
      })
      .catch((error) => {
        console.error(error);
        setStatus(error?.message || "Gagal simpan audio sirene ke server");
      })
      .finally(() => {
        event.target.value = "";
      });
  }
  async function playSirenAudio() {
    if (!sirenAudioUrl || !sirenAudio) {
      const toneOk = await startFallbackSirenTone();
      if (toneOk) {
        setStatus("Sirine aktif (fallback tone, MP3 belum tersedia)");
        return true;
      }
      setStatus("Pilih file MP3 untuk sirene dulu");
      return false;
    }
    try {
      await unlockSirenAudio();
      sirenAudio.currentTime = 0;
      await sirenAudio.play();
      updateSirenBtn();
      return true;
    } catch (error) {
      console.error(error);
      const toneOk = await startFallbackSirenTone();
      if (toneOk) return true;
      setStatus("Gagal memutar audio sirene (izin autoplay belum ada)");
      return false;
    }
  }
  function stopSirenAudio() {
    if (sirenAudio) {
      sirenAudio.pause();
      sirenAudio.currentTime = 0;
    }
    if (sirenToneOsc) {
      try { sirenToneOsc.stop(); } catch (_) {}
      try { sirenToneLfo.stop(); } catch (_) {}
      sirenToneOsc.disconnect();
      sirenToneLfo.disconnect();
      sirenToneLfoGain.disconnect();
      sirenToneGain.disconnect();
      sirenToneOsc = null;
      sirenToneLfo = null;
      sirenToneLfoGain = null;
      sirenToneGain = null;
    }
    updateSirenBtn();
  }
  async function toggleSirenAudioFromToolbar() {
    if (!sirenAudioUrl) {
      elSirenFileInput?.click();
      return;
    }
    if (isSirenActive()) {
      stopSirenAudio();
      if (sirenAutoMode && getAutoSirenTrigger()) {
        sirenManualMuteUntilMs = Date.now() + 30000; // snooze 30 detik
      }
      setStatus("Audio sirene dimatikan");
      return;
    }
    const hasOnlineDevice = appState.devices.some((d) => d.status === "online");
    if (!hasOnlineDevice) {
      setStatus("Audio sirene hanya bisa dinyalakan saat ada device online");
      return;
    }
    const ok = await playSirenAudio();
    if (ok) setStatus("Audio sirene dinyalakan");
  }
  function normalizeWidget(widget) {
    const w = defaultWidget({ ...widget, id: widget.id || generateId() });
    if (w.type === "level") {
      const a = Number(w.thAmanMax);
      const b = Number(w.thSiagaMax);
      const oldDefault = a === 50 && b === 100;
      if (oldDefault || !Number.isFinite(a) || !Number.isFinite(b) || a >= b) {
        w.thAmanMax = 101;
        w.thSiagaMax = 200;
      }
    }
    return w;
  }
  function hexToRgba(hex, alpha) {
    const clean = String(hex || "#ef4444").replace("#", "");
    if (clean.length !== 6) return `rgba(239,68,68,${alpha})`;
    const r = parseInt(clean.slice(0, 2), 16), g = parseInt(clean.slice(2, 4), 16), b = parseInt(clean.slice(4, 6), 16);
    return `rgba(${r},${g},${b},${alpha})`;
  }
  function computeChartYMax(datasets, th) {
    let maxVal = 0;
    for (const ds of datasets) {
      for (const v of ds.data) {
        if (v == null || v === "") continue;
        const n = Number(v);
        if (Number.isFinite(n)) maxVal = Math.max(maxVal, n);
      }
    }
    const thTop = Math.max(Number(th.normalCeil) || 0, Number(th.siagaMax) || 0);
    return Math.ceil(Math.max(maxVal * 1.12, thTop * 1.12, 20));
  }
  function segmentBorderByLevel(ctx, fallbackColor) {
    const v = ctx.p1.parsed.y;
    if (v == null || !Number.isFinite(v)) return fallbackColor;
    const t = getThresholds();
    if (v > t.siagaMax) return "#ef4444";
    if (v >= t.normalCeil) return "#f97316";
    return "#3b82f6";
  }
  function lineGradientFill(context, lineColor) {
    const chart = context.chart;
    const { ctx, chartArea } = chart;
    if (!chartArea) return hexToRgba(lineColor, 0.22);
    const g = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
    g.addColorStop(0, hexToRgba(lineColor, 0.4));
    g.addColorStop(1, hexToRgba(lineColor, 0.05));
    return g;
  }
  const floodChartThresholdPlugin = {
    id: "floodChartThresholds",
    beforeDatasetsDraw(chart) {
      const th = getThresholds();
      const y = chart.scales.y;
      const { ctx, chartArea } = chart;
      if (!y || !chartArea) return;
      const dark = document.documentElement.dataset.theme === "dark";
      const zones = [
        { lo: Math.max(0, y.min), hi: th.normalCeil, fill: dark ? "rgba(59,130,246,0.14)" : "rgba(59,130,246,0.22)" },
        { lo: th.normalCeil, hi: th.siagaMax, fill: dark ? "rgba(249,115,22,0.12)" : "rgba(249,115,22,0.16)" },
        { lo: th.siagaMax, hi: y.max, fill: dark ? "rgba(239,68,68,0.15)" : "rgba(239,68,68,0.18)" },
      ];
      ctx.save();
      ctx.beginPath();
      ctx.rect(chartArea.left, chartArea.top, chartArea.width, chartArea.height);
      ctx.clip();
      for (const z of zones) {
        const pLo = y.getPixelForValue(z.lo);
        const pHi = y.getPixelForValue(z.hi);
        const top = Math.min(pLo, pHi);
        const bottom = Math.max(pLo, pHi);
        const adjTop = Math.max(chartArea.top, top);
        const adjBottom = Math.min(chartArea.bottom, bottom);
        if (adjBottom <= adjTop) continue;
        ctx.fillStyle = z.fill;
        ctx.fillRect(chartArea.left, adjTop, chartArea.width, adjBottom - adjTop);
      }
      ctx.restore();
    },
    afterDatasetsDraw(chart) {
      const th = getThresholds();
      const y = chart.scales.y;
      const { ctx, chartArea } = chart;
      if (!y || !chartArea) return;
      const dark = document.documentElement.dataset.theme === "dark";
      ctx.save();
      ctx.setLineDash([5, 4]);
      ctx.lineWidth = 1;
      ctx.strokeStyle = dark ? "rgba(255,255,255,0.32)" : "rgba(0,0,0,0.18)";
      for (const v of [th.normalCeil, th.siagaMax]) {
        if (v < y.min || v > y.max) continue;
        const py = y.getPixelForValue(v);
        if (py < chartArea.top || py > chartArea.bottom) continue;
        ctx.beginPath();
        ctx.moveTo(chartArea.left, py);
        ctx.lineTo(chartArea.right, py);
        ctx.stroke();
      }
      ctx.setLineDash([4, 3]);
      ctx.lineWidth = 1.5;
      ctx.strokeStyle = dark ? "rgba(156,163,175,0.9)" : "rgba(75,85,99,0.8)";
      const meta0 = chart.getDatasetMeta(0);
      const len = meta0?.data?.length;
      const pt = len ? meta0.data[len - 1] : null;
      if (pt && Number.isFinite(pt.x)) {
        ctx.beginPath();
        ctx.moveTo(pt.x, chartArea.top);
        ctx.lineTo(pt.x, chartArea.bottom);
        ctx.stroke();
      }
      ctx.restore();
    },
  };

  function detectTheme() {
    const theme = localStorage.getItem("dashboard-theme") || "light";
    document.documentElement.setAttribute("data-theme", theme);
    elThemeBtn.innerHTML = theme === "dark"
      ? `<svg class="theme-icon" viewBox="0 0 24 24" aria-hidden="true">
          <circle cx="12" cy="12" r="4"></circle>
          <path d="M12 2v2"></path><path d="M12 20v2"></path>
          <path d="M4.93 4.93l1.41 1.41"></path><path d="M17.66 17.66l1.41 1.41"></path>
          <path d="M2 12h2"></path><path d="M20 12h2"></path>
          <path d="M4.93 19.07l1.41-1.41"></path><path d="M17.66 6.34l1.41-1.41"></path>
        </svg>`
      : `<svg class="theme-icon" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"></path>
        </svg>`;
  }
  function toggleTheme() {
    const current = document.documentElement.getAttribute("data-theme") || "light";
    const next = current === "light" ? "dark" : "light";
    document.documentElement.setAttribute("data-theme", next);
    localStorage.setItem("dashboard-theme", next);
    elThemeBtn.innerHTML = next === "dark"
      ? `<svg class="theme-icon" viewBox="0 0 24 24" aria-hidden="true">
          <circle cx="12" cy="12" r="4"></circle>
          <path d="M12 2v2"></path><path d="M12 20v2"></path>
          <path d="M4.93 4.93l1.41 1.41"></path><path d="M17.66 17.66l1.41 1.41"></path>
          <path d="M2 12h2"></path><path d="M20 12h2"></path>
          <path d="M4.93 19.07l1.41-1.41"></path><path d="M17.66 6.34l1.41-1.41"></path>
        </svg>`
      : `<svg class="theme-icon" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"></path>
        </svg>`;
    redrawAllCharts();
    refreshWidgetValues();
  }

  function getWidgetRows(widget) { return widget.device_id ? appState.sensorData.filter((r) => r.device_id === widget.device_id) : appState.sensorData; }
  function getLatestSensorValue(widget, fallback = 0) {
    const latest = getWidgetRows(widget)[0];
    if (!latest || latest[widget.field] === undefined || latest[widget.field] === null) return fallback;
    const num = Number(latest[widget.field]);
    return Number.isFinite(num) ? num : fallback;
  }
  function getThresholds() {
    const fromLevelWidget = appState.widgets.find((w) => w.type === "level");
    const normalCeil = Number(fromLevelWidget?.thAmanMax ?? 101);
    const siagaMax = Number(fromLevelWidget?.thSiagaMax ?? 200);
    const n = Number.isFinite(normalCeil) ? normalCeil : 101;
    const s = Number.isFinite(siagaMax) ? siagaMax : 200;
    return n < s ? { normalCeil: n, siagaMax: s } : { normalCeil: 101, siagaMax: 200 };
  }

  function getFloodLevel(value) {
    const water = Number(value || 0);
    const th = getThresholds();
    if (water > th.siagaMax) return { label: "AWAS", color: "#ef4444" };
    if (water >= th.normalCeil) return { label: "SIAGA", color: "#f97316" };
    return { label: "NORMAL", color: "#3b82f6" };
  }
  function getChartSeries(widget, limit = 60) {
    const points = Math.min(300, Math.max(10, Number(limit)));
    const rows = getWidgetRows(widget).slice(0, points).reverse();
    return {
      labels: rows.map((r) => new Date(r.created_at).toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" })),
      data: rows.map((r) => Number(r[widget.field || "water_level"] ?? 0)),
    };
  }
  function getDeviceLabel(widget) {
    if (!widget.device_id) return "Semua";
    const found = appState.devices.find((d) => d.device_id === widget.device_id);
    if (!found) return widget.device_id;
    return found.name ? `${found.device_id} (${found.name})` : found.device_id;
  }

  function syncChartDeviceSelect(widget) {
    const select = document.querySelector(`.chart-device-select[data-id="${widget.id}"]`);
    if (!select) return;

    const previous = select.value || widget.device_id || "";
    const options = [
      `<option value="">Semua perangkat</option>`,
      ...appState.devices.map((d) => {
        const label = d.name ? `${d.device_id} — ${d.name}` : d.device_id;
        return `<option value="${d.device_id}">${label}</option>`;
      }),
    ].join("");

    select.innerHTML = options;
    if (previous && appState.devices.some((d) => d.device_id === previous)) {
      select.value = previous;
    } else if (widget.device_id && appState.devices.some((d) => d.device_id === widget.device_id)) {
      select.value = widget.device_id;
    } else {
      select.value = "";
    }
  }

  function getLatestByDeviceMap() {
    const latestByDevice = {};
    for (const row of appState.sensorData) {
      if (!latestByDevice[row.device_id]) latestByDevice[row.device_id] = row;
    }
    return latestByDevice;
  }

  function renderDeviceStatusHtml() {
    if (!appState.devices.length) {
      return `<div class="device-status-empty">Belum ada device terdaftar.</div>`;
    }

    const latestByDevice = getLatestByDeviceMap();
    return appState.devices.map((dev) => {
      const latest = latestByDevice[dev.device_id];
      const water = Number(latest?.water_level ?? 0);
      const level = getFloodLevel(water);
      const statusClass = dev.status === "online" ? "online" : "offline";
      const statusText = dev.status === "online" ? "ONLINE" : "OFFLINE";
      return `
        <div class="device-status-item">
          <div class="device-status-top">
            <div>
              <div class="device-id">${dev.device_id}</div>
              <div class="device-meta">${dev.name || "-"} • ${dev.location || "-"}</div>
            </div>
            <div class="device-conn ${statusClass}">${statusText}</div>
          </div>
          <div class="device-water">
            <span>Tinggi Air: ${water.toFixed(1)} cm</span>
            <span style="color:${level.color};font-weight:700;">${level.label}</span>
          </div>
        </div>
      `;
    }).join("");
  }

  function renderGlobalStatusHtml() {
    const waters = appState.sensorData.map((r) => Number(r.water_level ?? 0)).filter((n) => Number.isFinite(n));
    const max = waters.length ? Math.max(...waters) : null;
    const level = max === null ? null : getFloodLevel(max);
    const maxRow = max === null ? null : appState.sensorData.find((r) => Number(r.water_level ?? 0) === max);

    const label = level ? level.label : "—";
    const color = level ? level.color : "var(--muted)";
    const detail = maxRow ? `${maxRow.device_id} — ${Number(maxRow.water_level).toFixed(1)} cm` : "Belum ada data sensor";

    return `
      <div class="global-status">
        <div class="global-status-label">STATUS SAAT INI</div>
        <div class="global-status-value" style="color:${color};">${label}</div>
        <div class="global-status-sub">${detail}</div>
      </div>
    `;
  }

  function renderDeviceCardsHtml() {
    if (!appState.devices.length) return `<div class="device-status-empty">Belum ada perangkat.</div>`;
    const latestByDevice = getLatestByDeviceMap();
    return `<div class="device-cards-grid">` + appState.devices.map((dev) => {
      const latest = latestByDevice[dev.device_id];
      const water = Number(latest?.water_level ?? 0);
      const level = getFloodLevel(water);
      const statusClass = dev.status === "online" ? "online" : "offline";
      return `
        <div class="device-card">
          <div class="device-card-top">
            <div>
              <div class="device-id">${dev.device_id}</div>
              <div class="device-meta">${dev.location || "-"}</div>
            </div>
            <div class="device-conn ${statusClass}">${dev.status === "online" ? "ONLINE" : "OFFLINE"}</div>
          </div>
          <div class="device-card-water">
            <div class="device-card-number">${water.toFixed(1)} <span class="device-card-unit">cm</span></div>
            <div class="device-card-level" style="color:${level.color};">${level.label}</div>
          </div>
        </div>
      `;
    }).join("") + `</div>`;
  }

  function renderAlertsHtml(limit = 30) {
    const items = (appState.logs || []).slice(0, limit);
    if (!items.length) return `<div class="device-status-empty">Belum ada log.</div>`;
    return `<div class="alerts-list">` + items.map((l) => {
      const ts = l.created_at ? new Date(l.created_at).toLocaleString("id-ID") : "-";
      const dev = l.device_id || "—";
      const action = l.action || "—";
      const detail = l.detail || "—";
      return `<div class="alerts-item"><div class="alerts-top"><span class="alerts-dev">${dev}</span><span class="alerts-time">${ts}</span></div><div class="alerts-action">${action}</div><div class="alerts-detail">${detail}</div></div>`;
    }).join("") + `</div>`;
  }

  function renderWaterHistoryHtml(widget, limit = 60) {
    const rows = getWidgetRows(widget).slice(0, limit);
    const deviceOptions = [
      `<option value="" ${!widget.device_id ? "selected" : ""}>Semua perangkat</option>`,
      ...appState.devices.map((d) => {
        const selected = widget.device_id === d.device_id ? "selected" : "";
        const label = d.name ? `${d.device_id} — ${d.name}` : d.device_id;
        return `<option value="${d.device_id}" ${selected}>${label}</option>`;
      }),
    ].join("");
    if (!rows.length) {
      return `
        <div class="chart-widget">
          <div class="chart-toolbar">
            <label class="chart-label">Perangkat</label>
            <select class="chart-device-select" data-action="water-history-device-select" data-id="${widget.id}">
              ${deviceOptions}
            </select>
          </div>
          <div class="device-status-empty">Belum ada riwayat ketinggian air.</div>
        </div>
      `;
    }
    return `
      <div class="chart-widget">
        <div class="chart-toolbar">
          <label class="chart-label">Perangkat</label>
          <select class="chart-device-select" data-action="water-history-device-select" data-id="${widget.id}">
            ${deviceOptions}
          </select>
        </div>
        <div class="alerts-list">
          ${rows.map((r) => {
            const ts = r.created_at ? new Date(r.created_at).toLocaleString("id-ID") : "-";
            const water = Number(r.water_level ?? 0);
            const rain = Number(r.rainfall ?? 0);
            const level = getFloodLevel(water);
            return `<div class="alerts-item">
              <div class="alerts-top"><span class="alerts-dev">${r.device_id || "—"}</span><span class="alerts-time">${ts}</span></div>
              <div class="alerts-action">Tinggi Air: <b>${water.toFixed(1)} cm</b> • Curah Hujan: ${rain.toFixed(1)} mm/h</div>
              <div class="alerts-detail">Status: <span style="color:${level.color};font-weight:700;">${level.label}</span></div>
            </div>`;
          }).join("")}
        </div>
      </div>
    `;
  }

  function renderCommandHistoryHtml(limit = 20) {
    const items = (appState.commands || []).slice(0, limit);
    if (!items.length) return `<div class="device-status-empty">Belum ada command.</div>`;
    return `
      <div class="alerts-list">
        ${items.map((c) => {
          const ts = c.created_at ? new Date(c.created_at).toLocaleString("id-ID") : "-";
          const dev = c.device_id || "—";
          const cmd = c.command || "—";
          const st = c.status || "—";
          return `<div class="alerts-item">
            <div class="alerts-top"><span class="alerts-dev">${dev}</span><span class="alerts-time">${ts}</span></div>
            <div class="alerts-action">command: <b>${cmd}</b></div>
            <div class="alerts-detail">status: ${st}</div>
          </div>`;
        }).join("")}
      </div>
    `;
  }

  function renderWorkerStatusHtml(limit = 30) {
    const items = (appState.workerStatuses || []).slice(0, limit);
    if (!items.length) return `<div class="device-status-empty">Belum ada status worker.</div>`;
    return `
      <div class="alerts-list">
        ${items.map((w) => {
          const ts = w.last_heartbeat_at ? new Date(w.last_heartbeat_at).toLocaleString("id-ID") : (w.updated_at ? new Date(w.updated_at).toLocaleString("id-ID") : "-");
          const worker = w.worker_id || "—";
          const dev = w.device_id || "—";
          const st = w.status || "—";
          const msg = w.message || "";
          return `<div class="alerts-item">
            <div class="alerts-top"><span class="alerts-dev">${worker}</span><span class="alerts-time">${ts}</span></div>
            <div class="alerts-action">${dev} • <b>${st}</b></div>
            <div class="alerts-detail">${msg}</div>
          </div>`;
        }).join("")}
      </div>
    `;
  }

  function renderSystemStatusHtml() {
    const online = appState.devices.filter((d) => d.status === "online").length;
    const total = appState.devices.length;
    const last = appState.sensorData[0]?.created_at ? new Date(appState.sensorData[0].created_at).toLocaleString("id-ID") : "—";
    return `
      <div class="system-status">
        <div class="system-row"><span>Total perangkat</span><b>${total}</b></div>
        <div class="system-row"><span>Online</span><b style="color:#22c55e;">${online}</b></div>
        <div class="system-row"><span>Offline</span><b style="color:#ef4444;">${Math.max(0, total - online)}</b></div>
        <div class="system-row"><span>Update terakhir</span><b>${last}</b></div>
      </div>
    `;
  }

  function renderThresholdSettingsHtml(widget) {
    const th = getThresholds();
    return `
      <div class="threshold-box">
        <div class="system-row"><span>Normal</span><b>air &lt; ${th.normalCeil} cm</b></div>
        <div class="system-row"><span>Siaga</span><b>${th.normalCeil} – ${th.siagaMax} cm</b></div>
        <div class="system-row"><span>Awas</span><b>air &gt; ${th.siagaMax} cm</b></div>
        <div class="hint-text" style="margin-top:8px;">Ubah nilai lewat menu Edit Widget (widget Level).</div>
      </div>
    `;
  }

  function renderMapPlaceholder(widget) {
    const lat = Number(widget.mapCenterLat ?? -6.2);
    const lng = Number(widget.mapCenterLng ?? 106.816666);
    const zoom = Number(widget.mapZoom ?? 11);
    const note = widget.mapNote || "Peta belum diaktifkan (placeholder).";
    return `
      <div class="map-box">
        <div class="map-title">Peta Lokasi</div>
        <div class="map-sub">Center: ${lat.toFixed(6)}, ${lng.toFixed(6)} • Zoom: ${zoom}</div>
        <div class="map-note">${note}</div>
        <div class="hint-text">Jika Anda mau peta beneran, saya bisa pasang Leaflet.</div>
      </div>
    `;
  }

  function widgetHeaderHtml(widget) {
    return `<div class="widget-header"><h4 class="widget-title"><span>${widget.title || widget.type.toUpperCase()}</span><span class="widget-meta">${getDeviceLabel(widget)}</span></h4><div class="widget-actions"><button class="icon-btn" data-action="edit" data-id="${widget.id}" title="Edit" aria-label="Edit widget"><svg class="widget-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5z"/></svg></button><button class="icon-btn" data-action="delete" data-id="${widget.id}" title="Hapus" aria-label="Hapus widget">X</button></div></div>`;
  }

  function refreshWidgetHeaderMeta(widget) {
    const root = document.querySelector(`.grid-stack-item-content[data-id="${widget.id}"]`);
    const meta = root?.querySelector(".widget-title .widget-meta");
    if (!meta) return;
    meta.textContent = getDeviceLabel(widget);
  }

  function renderWidgetBody(widget) {
    if (widget.type === "level") {
      const th = getThresholds();
      return `<div class="widget-body"><div class="level-bar">
        <div class="level-row"><span class="badge badge-normal">NORMAL &lt; ${th.normalCeil} cm</span><span class="badge badge-siaga">SIAGA ${th.normalCeil}–${th.siagaMax} cm</span><span class="badge badge-awas-hi">AWAS &gt; ${th.siagaMax} cm</span></div>
      </div></div>`;
    }

    if (widget.type === "chart_device") {
      const deviceOptions = [
        `<option value="" ${!widget.device_id ? "selected" : ""}>Semua perangkat</option>`,
        ...appState.devices.map((d) => {
          const selected = widget.device_id === d.device_id ? "selected" : "";
          const label = d.name ? `${d.device_id} — ${d.name}` : d.device_id;
          return `<option value="${d.device_id}" ${selected}>${label}</option>`;
        }),
      ].join("");

      return `<div class="widget-body">
        <div class="chart-widget">
          <div class="chart-toolbar">
            <label class="chart-label">Perangkat</label>
            <select class="chart-device-select" data-action="chart-device-select" data-id="${widget.id}">
              ${deviceOptions}
            </select>
          </div>
          <div class="chart-wrap"><canvas id="chart-${widget.id}"></canvas></div>
        </div>
      </div>`;
    }

    if (widget.type === "device_status") {
      return `<div class="widget-body device-status-wrap">${renderDeviceStatusHtml()}</div>`;
    }

    if (widget.type === "control_panel") {
      const opts = appState.devices.length
        ? appState.devices.map((d) => {
            const selected = widget.device_id === d.device_id ? "selected" : "";
            return `<option value="${d.device_id}" ${selected}>${[d.device_id, d.name].filter(Boolean).join(" — ")}</option>`;
          }).join("")
        : `<option value="">Belum ada perangkat</option>`;
      return `<div class="widget-body"><div class="control-panel" data-id="${widget.id}">
        <select class="control-select" data-role="device" data-action="control-device-select" data-id="${widget.id}">${opts}</select>
        <div class="control-grid">
          <button class="btn success control-btn" data-action="control" data-cmd="on" data-id="${widget.id}">Aktifkan</button>
          <button class="btn danger control-btn" data-action="control" data-cmd="off" data-id="${widget.id}">Nonaktifkan</button>
          <button class="btn secondary control-btn" data-action="control" data-cmd="alert" data-id="${widget.id}">${(sirenAudio && !sirenAudio.paused) ? "Sirine ON" : "Sirine OFF"}</button>
          <button class="btn secondary control-btn" data-action="control" data-cmd="reset" data-id="${widget.id}">Reset</button>
        </div>
        <div class="hint-text control-result" data-role="result"></div>
      </div></div>`;
    }

    if (widget.type === "water_history") {
      return `<div class="widget-body">${renderWaterHistoryHtml(widget)}</div>`;
    }

    if (widget.type === "command_history") {
      return `<div class="widget-body">${renderCommandHistoryHtml()}</div>`;
    }

    if (widget.type === "worker_status") {
      return `<div class="widget-body">${renderWorkerStatusHtml()}</div>`;
    }

    if (widget.type === "stat_online") {
      return `<div class="widget-body"><div class="statbox"><div class="stat-label">Device Online</div><div class="stat-value stat-green" data-role="online">—</div><div class="stat-sub">dari total <span data-role="total">—</span> device</div></div></div>`;
    }
    if (widget.type === "stat_avg") {
      return `<div class="widget-body"><div class="statbox"><div class="stat-label">Rata-rata Ketinggian</div><div class="stat-value stat-cyan" data-role="avg">—</div><div class="stat-sub">cm (semua sensor)</div></div></div>`;
    }
    if (widget.type === "stat_max") {
      return `<div class="widget-body"><div class="statbox"><div class="stat-label">Status Tertinggi</div><div class="stat-value" data-role="level">—</div><div class="stat-sub" data-role="dev">—</div></div></div>`;
    }
    if (widget.type === "stat_total") {
      return `<div class="widget-body"><div class="statbox"><div class="stat-label">Total Data</div><div class="stat-value stat-purple" data-role="count">—</div><div class="stat-sub">record sensor (100 terbaru)</div></div></div>`;
    }

    return `<div class="widget-body"><div class="device-status-empty">Widget belum didukung.</div></div>`;
  }

  function createWidgetNode(widget) {
    const node = document.createElement("div");
    node.className = "grid-stack-item";
    node.setAttribute("gs-id", widget.id);
    node.innerHTML = `<div class="grid-stack-item-content" data-id="${widget.id}">${widgetHeaderHtml(widget)}${renderWidgetBody(widget)}</div>`;
    return node;
  }

  function destroyWidgetChart(widgetId) { const chart = appState.charts.get(widgetId); if (chart) { chart.destroy(); appState.charts.delete(widgetId); } }
  function initOrUpdateChart(widget) {
    const canvas = document.getElementById(`chart-${widget.id}`);
    if (!canvas) return;
    const existing = appState.charts.get(widget.id);
    const dark = document.documentElement.dataset.theme === "dark";
    const tickColor = dark ? "#9ca3af" : "#374151";
    const gridColor = dark ? "#374151" : "#e5e7eb";
    const color = widget.chartColor || "#38bdf8";

    const points = Math.min(300, Math.max(10, Number(widget.chartPoints ?? 60)));
    const mode = widget.chartMode || "single";
    const th = getThresholds();

    let labels = [];
    let datasets = [];
    if (mode === "multi") {
      const devs = appState.devices.map((d) => d.device_id);
      const selected = widget.device_id ? [widget.device_id] : devs.slice(0, 4);
      const palette = ["#38bdf8", "#22c55e", "#f97316", "#a78bfa"];
      datasets = selected.map((devId, idx) => {
        const w = { ...widget, device_id: devId };
        const s = getChartSeries(w, points);
        labels = s.labels;
        const c = palette[idx % palette.length];
        return {
          label: `${devId} — tinggi air (cm)`,
          data: s.data,
          borderColor: c,
          backgroundColor: (ctx) => lineGradientFill(ctx, c),
          fill: true,
          tension: 0.35,
          pointRadius: 2,
          borderWidth: 2,
          segment: { borderColor: (ctx) => segmentBorderByLevel(ctx, c) },
        };
      });
    } else {
      const s = getChartSeries(widget, points);
      labels = s.labels;
      datasets = [
        {
          label: widget.title || "Ketinggian air (cm)",
          data: s.data,
          borderColor: color,
          backgroundColor: (ctx) => lineGradientFill(ctx, color),
          fill: true,
          tension: 0.35,
          pointRadius: 2,
          borderWidth: 2,
          segment: { borderColor: (ctx) => segmentBorderByLevel(ctx, color) },
        },
      ];
    }

    const yMax = computeChartYMax(datasets, th);
    const chartOptions = {
      maintainAspectRatio: false,
      animation: false,
      plugins: {
        legend: { labels: { color: dark ? "#e5e7eb" : "#111827" } },
        tooltip: {
          callbacks: {
            label(ctx) {
              const v = ctx.parsed.y;
              const cm = v == null || !Number.isFinite(v) ? "—" : `${Number(v).toFixed(1)} cm`;
              return `${ctx.dataset.label}: ${cm}`;
            },
          },
        },
      },
      scales: {
        x: {
          title: { display: true, text: "Waktu", color: tickColor, font: { size: 11 } },
          ticks: { color: tickColor, maxRotation: 45, minRotation: 0, autoSkip: true, maxTicksLimit: 12 },
          grid: { color: gridColor },
        },
        y: {
          beginAtZero: true,
          suggestedMax: yMax,
          title: { display: true, text: "cm", color: tickColor, font: { size: 11 } },
          ticks: { color: tickColor },
          grid: { color: gridColor },
        },
      },
    };

    if (existing) {
      existing.data.labels = labels;
      existing.data.datasets = datasets;
      existing.options.scales.y.suggestedMax = yMax;
      existing.options.scales.x.ticks.maxTicksLimit = 12;
      existing.update();
      return;
    }
    const chart = new Chart(canvas, {
      type: "line",
      data: { labels, datasets },
      plugins: [floodChartThresholdPlugin],
      options: chartOptions,
    });
    appState.charts.set(widget.id, chart);
  }
  function redrawAllCharts() { for (const widget of appState.widgets) if (widget.type === "chart_device") { destroyWidgetChart(widget.id); initOrUpdateChart(widget); } }

  function renderAllWidgets() {
    appState.grid.removeAll(false);
    appState.widgets = appState.widgets.map((w) => normalizeWidget(w));
    for (const widget of appState.widgets) appState.grid.addWidget(createWidgetNode(widget), { x: widget.x, y: widget.y, w: widget.w, h: widget.h, id: widget.id });
    for (const widget of appState.widgets) if (widget.type === "chart_device") initOrUpdateChart(widget);
  }
  function upsertWidgetInState(partialWidget) {
    const widget = normalizeWidget(partialWidget), idx = appState.widgets.findIndex((w) => w.id === widget.id);
    if (idx === -1) appState.widgets.push(widget); else appState.widgets[idx] = { ...appState.widgets[idx], ...widget };
  }
  async function removeWidgetById(widgetId) {
    destroyWidgetChart(widgetId);
    appState.widgets = appState.widgets.filter((w) => w.id !== widgetId);

    const gridItem = document.querySelector(`.grid-stack-item[gs-id="${widgetId}"]`);
    if (gridItem) {
      appState.grid.removeWidget(gridItem, true, true);
    }

    setStatus("Menyimpan perubahan...");
    await saveLayout();
  }
  function onGridChanged(items) {
    if (!items || !items.length) return;
    for (const item of items) {
      const id = item.id || item.el?.getAttribute("gs-id"), widget = appState.widgets.find((w) => w.id === id);
      if (!widget) continue;
      widget.x = item.x; widget.y = item.y; widget.w = item.w; widget.h = item.h;
    }
    queueSaveLayout();
  }

  function refreshDeviceSelect(selectedDeviceId = "") {
    const options = ['<option value="">Semua perangkat</option>'].concat(appState.devices.map((d) => {
      const selected = selectedDeviceId && selectedDeviceId === d.device_id ? "selected" : "";
      const label = d.name ? `${d.device_id} - ${d.name}` : d.device_id;
      return `<option value="${d.device_id}" ${selected}>${label}</option>`;
    })).join("");
    elWidgetDevice.innerHTML = options;
  }

  async function sendDeviceCommand(deviceId, command) {
    if (!deviceId || !command) return false;
    try {
      const response = await fetch(`${API_BASE}/command/send`, { method: "POST", headers: API_HEADERS, body: JSON.stringify({ device_id: deviceId, command }) });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      return true;
    } catch (error) {
      console.error(error);
      setStatus(`Gagal kirim command (${command}) ke ${deviceId}`);
      return false;
    }
  }

  async function fetchDashboardData() {
    try {
      const response = await fetch(`${API_BASE}/dashboard/data?chart_limit=60&online_timeout=12`, { headers: API_HEADERS });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const json = await response.json();
      appState.sensorData = json.latest_data || [];
      appState.devices = json.devices || [];
      appState.commands = json.commands || [];
      appState.workerStatuses = json.worker_statuses || [];

      refreshWidgetValues();
      if (!elWidgetModal.classList.contains("show")) refreshDeviceSelect();
    } catch (error) {
      console.error(error);
      setStatus("Gagal ambil data sensor");
    }
  }

  async function fetchLogs() {
    try {
      const response = await fetch(`${API_BASE}/dashboard/log`, { headers: API_HEADERS });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      appState.logs = await response.json();
      refreshWidgetValues();
    } catch (error) {
      console.error(error);
    }
  }

  function refreshWidgetValues() {
    const online = appState.devices.filter((d) => d.status === "online").length;
    const total = appState.devices.length;
    const waters = appState.sensorData.map((r) => Number(r.water_level ?? 0)).filter((n) => Number.isFinite(n));
    const avg = waters.length ? (waters.reduce((a, b) => a + b, 0) / waters.length) : null;
    const max = waters.length ? Math.max(...waters) : null;
    const maxRow = max === null ? null : appState.sensorData.find((r) => Number(r.water_level ?? 0) === max);
    const maxLevel = max === null ? null : getFloodLevel(max);

    for (const widget of appState.widgets) {
      const root = document.querySelector(`.grid-stack-item-content[data-id="${widget.id}"]`);
      if (!root) continue;
      if (widget.type === "level") continue;
      if (widget.type === "chart_device") {
        syncChartDeviceSelect(widget);
        refreshWidgetHeaderMeta(widget);
        initOrUpdateChart(widget);
        continue;
      }
      if (widget.type === "device_status") { root.querySelector(".device-status-wrap").innerHTML = renderDeviceStatusHtml(); continue; }

      if (widget.type === "command_history") {
        root.querySelector(".widget-body").innerHTML = renderCommandHistoryHtml();
        continue;
      }

      if (widget.type === "water_history") {
        root.querySelector(".widget-body").innerHTML = renderWaterHistoryHtml(widget);
        refreshWidgetHeaderMeta(widget);
        continue;
      }

      if (widget.type === "worker_status") {
        root.querySelector(".widget-body").innerHTML = renderWorkerStatusHtml();
        continue;
      }

      if (widget.type === "control_panel") {
        const sel = root.querySelector("select[data-role='device']");
        if (sel) {
          const prev = sel.value;
          if (appState.devices.length) {
            sel.innerHTML = appState.devices
              .map(d => `<option value="${d.device_id}">${[d.device_id, d.name].filter(Boolean).join(" — ")}</option>`)
              .join("");
            // keep previous selection if still exists
            if (prev && appState.devices.some((d) => d.device_id === prev)) {
              sel.value = prev;
            } else if (widget.device_id && appState.devices.some((d) => d.device_id === widget.device_id)) {
              sel.value = widget.device_id;
            }
          } else {
            sel.innerHTML = `<option value="">Belum ada perangkat</option>`;
          }
        }
        refreshWidgetHeaderMeta(widget);
        refreshSirenControlButtons();
        continue;
      }

      if (widget.type === "stat_online") {
        const a = root.querySelector("[data-role='online']");
        const t = root.querySelector("[data-role='total']");
        if (a) a.textContent = String(online);
        if (t) t.textContent = String(total);
        continue;
      }
      if (widget.type === "stat_avg") {
        const n = root.querySelector("[data-role='avg']");
        if (n) n.textContent = avg === null ? "—" : avg.toFixed(1);
        continue;
      }
      if (widget.type === "stat_total") {
        const n = root.querySelector("[data-role='count']");
        if (n) n.textContent = String(appState.sensorData.length);
        continue;
      }
      if (widget.type === "stat_max") {
        const l = root.querySelector("[data-role='level']");
        const d = root.querySelector("[data-role='dev']");
        if (l) l.innerHTML = maxLevel ? `<span style="color:${maxLevel.color};font-weight:900;">${maxLevel.label}</span>` : "—";
        if (d) d.textContent = maxRow ? `${maxRow.device_id} — ${Number(maxRow.water_level).toFixed(1)} cm` : "—";
        continue;
      }
    }
    evaluateAutoSiren();
  }

  function updateModalVisibilityByType() {
    const type = elWidgetType.value;
    if (elChartColorField) elChartColorField.style.display = type === "chart_device" ? "block" : "none";
    if (elChartConfigGroup) elChartConfigGroup.style.display = type === "chart_device" ? "grid" : "none";
    if (elThresholdConfigGroup) elThresholdConfigGroup.style.display = type === "level" ? "grid" : "none";
    if (elControlConfigGroup) elControlConfigGroup.style.display = type === "control_panel" ? "grid" : "none";
    if (elGaugeConfigGroup) elGaugeConfigGroup.style.display = "none";
    if (elToggleConfigGroup) elToggleConfigGroup.style.display = "none";
    if (elMapConfigGroup) elMapConfigGroup.style.display = "none";
  }

  function openModal(editWidget = null) {
    appState.editId = editWidget ? editWidget.id : null;
    elModalTitle.textContent = editWidget ? "Edit Widget" : "Tambah Widget";
    refreshDeviceSelect(editWidget?.device_id || "");
    elWidgetType.value = editWidget?.type || "chart_device";
    elWidgetTitle.value = editWidget?.title || "";
    elWidgetField.value = editWidget?.field || "water_level";
    elWidgetUnit.value = editWidget?.unit || "";
    elWidgetDevice.value = editWidget?.device_id || "";
    elWidgetChartColor.value = editWidget?.chartColor || "#ef4444";
    elWidgetGaugeMin.value = Number(editWidget?.gaugeMin ?? 0);
    elWidgetGaugeMax.value = Number(editWidget?.gaugeMax ?? 100);
    elWidgetCmdOn.value = editWidget?.commandOn || "start";
    elWidgetCmdOff.value = editWidget?.commandOff || "stop";
    if (elWidgetCmdAlert) elWidgetCmdAlert.value = editWidget?.commandAlert || "alert";
    if (elWidgetCmdReset) elWidgetCmdReset.value = editWidget?.commandReset || "reset";
    elWidgetChartPoints.value = Number(editWidget?.chartPoints ?? 60);
    elWidgetChartMode.value = editWidget?.chartMode || "single";
    elThAmanMax.value = Number(editWidget?.thAmanMax ?? 101);
    elThSiagaMax.value = Number(editWidget?.thSiagaMax ?? 200);
    updateModalVisibilityByType();
    elWidgetModal.classList.add("show");
  }
  function closeModal() { elWidgetModal.classList.remove("show"); appState.editId = null; }

  function saveModalWidget() {
    const type = elWidgetType.value, min = Number(elWidgetGaugeMin.value), max = Number(elWidgetGaugeMax.value);
    const payload = normalizeWidget({
      id: appState.editId || generateId(),
      type,
      title: (elWidgetTitle.value || type.toUpperCase()).trim(),
      field: elWidgetField.value,
      unit: elWidgetUnit.value.trim(),
      device_id: elWidgetDevice.value,
      chartColor: elWidgetChartColor.value,
      chartPoints: Number(elWidgetChartPoints.value || 60),
      chartMode: elWidgetChartMode.value || "single",
      gaugeMin: Number.isFinite(min) ? min : 0,
      gaugeMax: Number.isFinite(max) && max > min ? max : (Number.isFinite(min) ? min + 1 : 100),
      commandOn: (elWidgetCmdOn.value || "start").trim(),
      commandOff: (elWidgetCmdOff.value || "stop").trim(),
      commandAlert: (elWidgetCmdAlert?.value || "alert").trim(),
      commandReset: (elWidgetCmdReset?.value || "reset").trim(),
      thAmanMax: Number(elThAmanMax.value || 101),
      thSiagaMax: Number(elThSiagaMax.value || 200)
    });

    if (appState.editId) {
      const old = appState.widgets.find((w) => w.id === appState.editId);
      if (!old) return closeModal();
      payload.x = old.x; payload.y = old.y; payload.w = old.w; payload.h = old.h; payload.state = old.state; payload.value = old.value;
      upsertWidgetInState(payload);
      appState.grid.removeWidget(`[gs-id="${payload.id}"]`, false, false);
      appState.grid.addWidget(createWidgetNode(payload), { x: payload.x, y: payload.y, w: payload.w, h: payload.h, id: payload.id });
      if (payload.type === "chart_device") initOrUpdateChart(payload);
    } else {
      payload.x = 0;
      payload.y = 0;
      payload.w = (payload.type === "chart_device" || payload.type === "water_history") ? 6 : 3;
      payload.h = (payload.type === "chart_device" || payload.type === "water_history") ? 4 : 3;
      appState.widgets.push(payload);
      appState.grid.addWidget(createWidgetNode(payload), {
        w: payload.w,
        h: payload.h,
        id: payload.id,
        autoPosition: true
      });
      if (payload.type === "chart_device") initOrUpdateChart(payload);

      // Sync persisted x/y/w/h from actual placed node
      const placed = appState.grid.getGridItems().find((el) => {
        const node = el.gridstackNode;
        const id = node?.id ?? el.getAttribute("gs-id");
        return String(id) === String(payload.id);
      });
      if (placed?.gridstackNode) {
        payload.x = placed.gridstackNode.x;
        payload.y = placed.gridstackNode.y;
        payload.w = placed.gridstackNode.w;
        payload.h = placed.gridstackNode.h;
      }
    }
    queueSaveLayout();
    closeModal();
  }

  function buildLayoutPayload() {
    const map = new Map();
    const items = appState.grid.getGridItems();
    items.forEach((el) => {
      const node = el.gridstackNode;
      const id = node?.id ?? el.getAttribute("gs-id");
      if (!id || !node) return;
      map.set(String(id), { x: node.x, y: node.y, w: node.w, h: node.h });
    });

    return appState.widgets.map((w) => {
      const pos = map.get(String(w.id));
      return { ...w, x: pos ? pos.x : w.x, y: pos ? pos.y : w.y, w: pos ? pos.w : w.w, h: pos ? pos.h : w.h };
    });
  }
  async function saveLayout() {
    try {
      appState.widgets = buildLayoutPayload().map((w) => normalizeWidget(w));
      const response = await fetch(`${API_BASE}/dashboard/layout`, { method: "POST", headers: API_HEADERS, body: JSON.stringify({ layout: appState.widgets }) });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      localStorage.setItem("dashboard-layout-backup", JSON.stringify(appState.widgets));
      setStatus(`Layout tersimpan (${new Date().toLocaleTimeString("id-ID")})`);
    } catch (error) {
      console.error(error);
      setStatus("Gagal simpan layout");
    }
  }
  function queueSaveLayout() { setStatus("Menyimpan layout..."); clearTimeout(appState.saveTimer); appState.saveTimer = setTimeout(saveLayout, 550); }

  async function loadLayout() {
    try {
      const response = await fetch(`${API_BASE}/dashboard/layout`, { headers: API_HEADERS });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const json = await response.json();
      let layoutPayload = json.layout;
      if (typeof layoutPayload === "string") {
        try { layoutPayload = JSON.parse(layoutPayload); } catch (_) { layoutPayload = null; }
      }

      if (Array.isArray(layoutPayload)) {
        appState.widgets = layoutPayload.map((w) => normalizeWidget(w));
      } else if (layoutPayload === null) {
        const backup = localStorage.getItem("dashboard-layout-backup");
        if (backup) {
          try {
            const parsed = JSON.parse(backup);
            appState.widgets = Array.isArray(parsed) ? parsed.map((w) => normalizeWidget(w)) : DEFAULT_LAYOUT.map((w) => normalizeWidget(w));
          } catch (_) {
            appState.widgets = DEFAULT_LAYOUT.map((w) => normalizeWidget(w));
          }
        } else {
          appState.widgets = DEFAULT_LAYOUT.map((w) => normalizeWidget(w));
        }
      } else {
        appState.widgets = DEFAULT_LAYOUT.map((w) => normalizeWidget(w));
      }

      renderAllWidgets();
      setStatus("Layout");
    } catch (error) {
      console.error(error);
      const backup = localStorage.getItem("dashboard-layout-backup");
      if (backup) {
        try {
          const parsed = JSON.parse(backup);
          appState.widgets = Array.isArray(parsed) ? parsed.map((w) => normalizeWidget(w)) : DEFAULT_LAYOUT.map((w) => normalizeWidget(w));
        } catch (_) {
          appState.widgets = DEFAULT_LAYOUT.map((w) => normalizeWidget(w));
        }
      } else {
        appState.widgets = DEFAULT_LAYOUT.map((w) => normalizeWidget(w));
      }
      renderAllWidgets();
      setStatus("Layout dari backup/default aktif");
    }
  }
  async function resetLayout() {
    if (!confirm("Reset layout ke default?")) return;
    try {
      const response = await fetch(`${API_BASE}/dashboard/layout`, { method: "DELETE", headers: API_HEADERS });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
    } catch (error) { console.error(error); }

    // Clear everything immediately in UI (no refresh needed)
    appState.charts.forEach((chart) => chart.destroy());
    appState.charts.clear();
    appState.grid.removeAll(true);
    appState.widgets = DEFAULT_LAYOUT.map((w) => normalizeWidget(w));
    renderAllWidgets();

    localStorage.removeItem("dashboard-layout-backup");
    setStatus("Layout direset. Menyimpan...");
    await saveLayout();
  }
  function toggleLockLayout() {
    setLockState(!appState.locked);
  }

  function setLockState(locked) {
    appState.locked = Boolean(locked);
    appState.grid.enableMove(!appState.locked);
    appState.grid.enableResize(!appState.locked);
    document.body.classList.toggle("layout-locked", appState.locked);
    localStorage.setItem("dashboard-layout-locked", appState.locked ? "1" : "0");
    elLockBtn.innerHTML = appState.locked
      ? `<svg class="lock-icon lock-closed" viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2" ry="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>`
      : `<svg class="lock-icon lock-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M17 8h-1V6a4 4 0 0 0-7.87-1"/><rect x="5" y="8" width="14" height="12" rx="2" ry="2"/></svg>`;
  }

  function setupEvents() {
    const armAudio = () => { unlockSirenAudio(); };
    window.addEventListener("pointerdown", armAudio, { once: true });
    window.addEventListener("keydown", armAudio, { once: true });
    elThemeBtn.addEventListener("click", toggleTheme);
    elSirenBtn.addEventListener("click", toggleSirenAudioFromToolbar);
    elSirenModeBtn.addEventListener("click", toggleSirenMode);
    elSirenFileInput.addEventListener("change", handleSirenFileSelected);
    document.getElementById("addWidgetBtn").addEventListener("click", () => openModal(null));
    document.getElementById("resetBtn").addEventListener("click", resetLayout);
    document.getElementById("lockBtn").addEventListener("click", toggleLockLayout);
    document.getElementById("cancelWidgetBtn").addEventListener("click", closeModal);
    document.getElementById("saveWidgetBtn").addEventListener("click", saveModalWidget);
    elWidgetType.addEventListener("change", updateModalVisibilityByType);

    elWidgetModal.addEventListener("click", (event) => { if (event.target === elWidgetModal) closeModal(); });

    elGrid.addEventListener("click", async (event) => {
      const btn = event.target.closest("[data-action]");
      if (!btn) return;
      const action = btn.dataset.action, widgetId = btn.dataset.id, widget = appState.widgets.find((w) => String(w.id) === String(widgetId));
      if (!widget) return;

      if (action === "delete") { if (confirm(`Hapus widget '${widget.title}'?`)) await removeWidgetById(widget.id); }
      if (action === "edit") { openModal(widget); }
      if (action === "toggle") {
        if (!widget.device_id) { setStatus("Widget button butuh device agar command bisa dikirim"); return; }
        const nextState = !widget.state, cmd = nextState ? widget.commandOn : widget.commandOff;
        const ok = await sendDeviceCommand(widget.device_id, cmd);
        if (!ok) return;
        widget.state = nextState;
        btn.classList.toggle("on", widget.state);
        btn.textContent = widget.state ? "ON" : "OFF";
        setStatus(`Command '${cmd}' dikirim ke ${widget.device_id}`);
        queueSaveLayout();
      }

      if (action === "control") {
        const panel = btn.closest(".control-panel");
        const sel = panel?.querySelector("select[data-role='device']");
        const result = panel?.querySelector("[data-role='result']");
        const deviceId = sel?.value;
        if (!deviceId) { if (result) result.textContent = "Pilih perangkat dulu."; return; }

        const cmdKey = btn.dataset.cmd;
        const cmd = cmdKey === "on" ? widget.commandOn
          : cmdKey === "off" ? widget.commandOff
          : cmdKey === "alert" ? widget.commandAlert
          : widget.commandReset;

        if (cmdKey === "alert" && !isDeviceOnline(deviceId)) {
          if (result) result.textContent = `Device ${deviceId} offline. Sirine hanya untuk device online.`;
          setStatus(`Sirine ditolak: ${deviceId} offline`);
          return;
        }

        if (cmdKey === "alert") {
          const sirenIsOn = isSirenActive();
          if (sirenIsOn) {
            stopSirenAudio();
            if (sirenAutoMode && getAutoSirenTrigger()) {
              sirenManualMuteUntilMs = Date.now() + 30000; // snooze 30 detik
            }
            if (result) result.textContent = `Sirine OFF untuk ${deviceId}`;
            setStatus(`Sirine dimatikan (${deviceId})`);
            return;
          }
        }

        const ok = await sendDeviceCommand(deviceId, cmd);
        if (ok && cmdKey === "alert") {
          await playSirenAudio();
        }
        if (ok && (cmdKey === "off" || cmdKey === "reset")) {
          stopSirenAudio();
        }
        if (result) result.textContent = ok ? `Perintah '${cmd}' dikirim ke ${deviceId}` : `Gagal kirim '${cmd}'`;
      }
    });

    elGrid.addEventListener("input", (event) => {
      const input = event.target.closest("[data-action='slider']");
      if (!input) return;
      const widget = appState.widgets.find((w) => w.id === input.dataset.id);
      if (!widget) return;
      widget.value = Number(input.value);
      const display = input.closest(".grid-stack-item-content")?.querySelector(".rpm-value");
      if (display) display.textContent = `${widget.value}${widget.unit || "RPM"}`;
      queueSaveLayout();
    });

    elGrid.addEventListener("change", (event) => {
      const select = event.target.closest("[data-action='chart-device-select']");
      if (select) {
        const widgetId = select.dataset.id;
        const widget = appState.widgets.find((w) => String(w.id) === String(widgetId));
        if (!widget) return;

        widget.device_id = select.value || "";
        refreshWidgetHeaderMeta(widget);
        destroyWidgetChart(widget.id);
        initOrUpdateChart(widget);
        queueSaveLayout();
        return;
      }

      const controlSelect = event.target.closest("[data-action='control-device-select']");
      if (controlSelect) {
        const controlWidgetId = controlSelect.dataset.id;
        const controlWidget = appState.widgets.find((w) => String(w.id) === String(controlWidgetId));
        if (!controlWidget) return;

        controlWidget.device_id = controlSelect.value || "";
        refreshWidgetHeaderMeta(controlWidget);
        queueSaveLayout();
        return;
      }

      const waterHistorySelect = event.target.closest("[data-action='water-history-device-select']");
      if (!waterHistorySelect) return;

      const waterWidgetId = waterHistorySelect.dataset.id;
      const waterWidget = appState.widgets.find((w) => String(w.id) === String(waterWidgetId));
      if (!waterWidget) return;

      waterWidget.device_id = waterHistorySelect.value || "";
      refreshWidgetHeaderMeta(waterWidget);
      const root = document.querySelector(`.grid-stack-item-content[data-id="${waterWidget.id}"]`);
      if (root) root.querySelector(".widget-body").innerHTML = renderWaterHistoryHtml(waterWidget);
      queueSaveLayout();
    });

    appState.grid.on("change", (_, items) => onGridChanged(items));
    appState.grid.on("dragstop", () => {
      clearTimeout(appState.saveTimer);
      saveLayout();
    });
    appState.grid.on("resizestop", () => {
      clearTimeout(appState.saveTimer);
      saveLayout();
    });
  }

  async function boot() {
    detectTheme();
    const savedAutoMode = localStorage.getItem("siren-auto-mode");
    setSirenMode(savedAutoMode === null ? true : savedAutoMode === "1");
    updateSirenBtn();
    appState.grid = GridStack.init({
      column: 12,
      cellHeight: 90,
      margin: 8,
      animate: true,
      float: true,
      resizable: { handles: "all" },
      draggable: { handle: ".widget-header", cancel: ".widget-actions,.icon-btn,button,input,select,textarea,canvas" }
    }, "#dashboardGrid");
    setupEvents();
    const savedLocked = localStorage.getItem("dashboard-layout-locked") === "1";
    setLockState(savedLocked);
    await fetchDashboardData();
    await loadSirenAudioFromServer();
    await loadLayout();
    setLockState(savedLocked);
    await fetchDashboardData();
    await fetchLogs();
    setInterval(fetchDashboardData, 5000);
    setInterval(fetchLogs, 5000);
    setInterval(() => { evaluateAutoSiren(); }, 1000);
  }
  boot();
</script>
</body>
</html>

