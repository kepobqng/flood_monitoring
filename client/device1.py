import requests
import time
import random

API_BASE = "http://flood_monitoring.test/api"
API_KEY = "FLOOD-SECRET-KEY-2025"
DEVICE_ID = "DEV001"

HEADERS = {"X-API-KEY": API_KEY, "Content-Type": "application/json"}


def send_ingest():
    payload = {
        "device_id": DEVICE_ID,
        "water_level": round(random.uniform(10, 200), 2),
        "rainfall": round(random.uniform(0, 50), 2),
    }
    try:
        r = requests.post(f"{API_BASE}/ingest", json=payload, headers=HEADERS, timeout=5)
        print(f"[{DEVICE_ID}] Sent {payload} -> {r.status_code}")
    except Exception as e:
        print(f"[{DEVICE_ID}] Error ingest: {e}")


def run():
    print(f"[{DEVICE_ID}] Simulator aktif. Mengirim data berkala...")
    while True:
        send_ingest()
        time.sleep(5)


if __name__ == "__main__":
    run()
