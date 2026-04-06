import requests, time, random

API_URL = "http://flood_monitoring.test/api/ingest"
API_KEY  = "FLOOD-SECRET-KEY-2025"
DEVICE_ID = "DEV002"   # DEV002, DEV003 untuk device lain

HEADERS = {"X-API-KEY": API_KEY, "Content-Type": "application/json"}

def simulate():
    while True:
        payload = {
            "device_id":   DEVICE_ID,
            "water_level": round(random.uniform(10, 200), 2),  # cm
            "rainfall":    round(random.uniform(0, 50), 2),    # mm/h
        }
        try:
            r = requests.post(API_URL, json=payload, headers=HEADERS)
            print(f"[{DEVICE_ID}] Sent: {payload} → {r.json()}")
        except Exception as e:
            print(f"Error: {e}")
        time.sleep(5)  # kirim tiap 5 detik

if __name__ == "__main__":
    simulate()
