import requests, time

API_BASE = "http://flood_monitoring.test/api"
API_KEY  = "FLOOD-SECRET-KEY-2025"
DEVICE_ID = "DEV001"

HEADERS = {"X-API-KEY": API_KEY, "Content-Type": "application/json"}

def poll():
    while True:
        try:
            # Ambil command pending
            r = requests.get(f"{API_BASE}/command/get",
                             params={"device_id": DEVICE_ID},
                             headers=HEADERS)
            cmd = r.json()

            if cmd:
                print(f"[WORKER] Command diterima: {cmd['command']}")

                # Eksekusi logika
                if cmd['command'] == 'start':
                    print("[WORKER] Monitoring DIMULAI")
                elif cmd['command'] == 'stop':
                    print("[WORKER] Monitoring DIHENTIKAN")
                elif cmd['command'] == 'alert':
                    print("[WORKER] ⚠️  SIRINE BANJIR DIAKTIFKAN!")

                # Lapor selesai ke API
                requests.post(f"{API_BASE}/command/done",
                              json={"id": cmd['id'], "device_id": DEVICE_ID},
                              headers=HEADERS)
                print(f"[WORKER] Command ID {cmd['id']} → executed")
            else:
                print("[WORKER] Tidak ada command, polling...")

        except Exception as e:
            print(f"[WORKER] Error: {e}")

        time.sleep(3)  # polling tiap 3 detik

if __name__ == "__main__":
    poll()
