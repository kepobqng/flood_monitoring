import time
import requests
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
WORKER_ID = "worker-main"
DEVICE_IDS = ["DEV001", "DEV002", "DEV003"]
POLL_INTERVAL_SECONDS = 3

HEADERS = {"X-API-KEY": API_KEY, "Content-Type": "application/json"}


def update_status(device_id, status, message=""):
    try:
        requests.post(
            f"{API_BASE}/status/update",
            json={
                "worker_id": WORKER_ID,
                "device_id": device_id,
                "status": status,
                "message": message,
            },
            headers=HEADERS,
            timeout=5,
        )
    except Exception as e:
        print(f"[WORKER] gagal update status {device_id}: {e}")


def fetch_command(device_id):
    response = requests.get(
        f"{API_BASE}/command/get",
        params={"device_id": device_id},
        headers=HEADERS,
        timeout=5,
    )
    if not response.ok:
        raise RuntimeError(f"fetch command {device_id} gagal ({response.status_code})")
    return response.json()


def mark_done(command_id, device_id):
    response = requests.post(
        f"{API_BASE}/command/done",
        json={"id": command_id, "device_id": device_id},
        headers=HEADERS,
        timeout=5,
    )
    if not response.ok:
        raise RuntimeError(f"mark done {device_id} gagal ({response.status_code})")


def execute_command(device_id, command):
    if command == "start":
        print(f"[WORKER] {device_id}: Monitoring DIMULAI")
        return "running", "monitoring started"
    if command == "stop":
        print(f"[WORKER] {device_id}: Monitoring DIHENTIKAN")
        return "stopped", "monitoring stopped"
    if command == "alert":
        print(f"[WORKER] {device_id}: ALERT sirine banjir AKTIF")
        return "alerting", "alert command executed"
    if command == "reset":
        print(f"[WORKER] {device_id}: RESET sistem")
        return "stopped", "system reset executed"

    print(f"[WORKER] {device_id}: command tidak dikenal -> {command}")
    return "unknown_command", f"unknown command: {command}"


def poll():
    print(f"[WORKER] start polling multi-device: {', '.join(DEVICE_IDS)}")
    while True:
        for device_id in DEVICE_IDS:
            try:
                update_status(device_id, "idle", "polling command")
                cmd = fetch_command(device_id)

                if not cmd:
                    print(f"[WORKER] {device_id}: tidak ada command")
                    continue

                command_id = cmd.get("id")
                command = cmd.get("command", "")
                print(f"[WORKER] {device_id}: command diterima -> {command}")
                status, message = execute_command(device_id, command)
                update_status(device_id, status, message)

                if command_id is not None:
                    mark_done(command_id, device_id)
                    print(f"[WORKER] {device_id}: command ID {command_id} -> executed")
            except Exception as e:
                print(f"[WORKER] {device_id}: error -> {e}")
                update_status(device_id, "error", str(e))

        time.sleep(POLL_INTERVAL_SECONDS)


if __name__ == "__main__":
    poll()
