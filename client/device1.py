import requests
import time
import random
import os

API_KEY = os.getenv("FLOOD_API_KEY", "FLOOD-SECRET-KEY-2025")


def resolve_api_base(api_key: str) -> str:
    env_base = (os.getenv("FLOOD_API_BASE") or "").strip().rstrip("/")
    candidates = [env_base] if env_base else []
    candidates += ["http://127.0.0.1:8000/api", "http://flood_monitoring.test/api"]

    seen = set()
    for base in candidates:
        base = (base or "").strip().rstrip("/")
        if not base or base in seen:
            continue
        seen.add(base)
        try:
            requests.get(f"{base}/dashboard/data", headers={"X-API-KEY": api_key}, timeout=2.5)
            return base
        except Exception:
            continue

    return env_base or "http://127.0.0.1:8000/api"


API_BASE = resolve_api_base(API_KEY)
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
