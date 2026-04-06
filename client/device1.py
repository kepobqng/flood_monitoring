import requests
import time
import random

API_URL = "http://flood_monitoring.test/api/ingest"
API_KEY = "FLOOD-SECRET-KEY-2025"
DEVICE_ID = "DEV001"

HEADERS = {"X-API-KEY": API_KEY, "Content-Type": "application/json"}

def simulate():
    while True:
        payload = {
            "device_id": DEVICE_ID,
            "water_level": round(random.uniform(10, 200), 2),
            "rainfall": round(random.uniform(0, 50), 2),
        }
        try:
            r = requests.post(API_URL, json=payload, headers=HEADERS)
            print("Status: " + str(r.status_code))
            print("Response: " + r.text)
        except Exception as e:
            print("Error: " + str(e))
        time.sleep(5)

if __name__ == "__main__":
    simulate()