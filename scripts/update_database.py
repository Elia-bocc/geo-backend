import requests
import psycopg2
from datetime import datetime, timedelta

API_KEY = "c59356581429ee7ba8be1ac1f713ae7e"

# Aiven DB credentials
DB_HOST = "pg-f9ef4a9-pgdunk-2799.e.aivencloud.com"
DB_PORT = 13578
DB_NAME = "defaultdb"
DB_USER = "avnadmin"
DB_PASS = "AVNS_dP-S1FX9jwwx5uKFUFB"

# Grid coordinates
base_coord = (38.6864, -9.2286)
top_lat = 38.8092
right_lon = -9.0781
inc_lat = (top_lat - base_coord[0]) / 10
inc_lon = (right_lon - base_coord[1]) / 15
coordinates = [(base_coord[0] + i * inc_lat, base_coord[1] + j * inc_lon)
               for i in range(10) for j in range(15)]

# API call
def get_air_quality_data(lat, lon):
    url = f"http://api.openweathermap.org/data/2.5/air_pollution?lat={lat}&lon={lon}&appid={API_KEY}"
    response = requests.get(url)
    response.raise_for_status()
    return response.json()

# DB insert/update
def upsert_data(conn, lat, lon, data):
    aqi = data['list'][0]['main']['aqi']
    comp = data['list'][0]['components']
    pm2_5 = comp['pm2_5']
    pm10 = comp['pm10']
    no2 = comp['no2']
    co = comp['co']
    o3 = comp['o3']
    timestamp = datetime.utcfromtimestamp(data['list'][0]['dt'])

    sql = """
    INSERT INTO "public"."Air Pollution" (geom, pm2_5, pm10, no2, co, o3, aqi, timestamp)
    VALUES (
        ST_SetSRID(ST_MakePoint(%s, %s), 4326),
        %s, %s, %s, %s, %s, %s, %s
    )
    ON CONFLICT (geom, timestamp) DO UPDATE
    SET pm2_5 = EXCLUDED.pm2_5,
        pm10  = EXCLUDED.pm10,
        no2   = EXCLUDED.no2,
        co    = EXCLUDED.co,
        o3    = EXCLUDED.o3,
        aqi   = EXCLUDED.aqi;
    """
    with conn.cursor() as cur:
        cur.execute(sql, (lon, lat, pm2_5, pm10, no2, co, o3, aqi, timestamp))
    conn.commit()

# Cleanup old data
def cleanup_old_data(conn, days=7):
    cutoff = datetime.utcnow() - timedelta(days=days)
    sql = 'DELETE FROM "public"."Air Pollution" WHERE timestamp < %s'
    with conn.cursor() as cur:
        cur.execute(sql, (cutoff,))
    conn.commit()
    print(f"Deleted records older than {days} days")

def main():
    conn = psycopg2.connect(
        host=DB_HOST, port=DB_PORT, dbname=DB_NAME,
        user=DB_USER, password=DB_PASS, sslmode="require"
    )

    for lat, lon in coordinates:
        try:
            data = get_air_quality_data(lat, lon)
            upsert_data(conn, lat, lon, data)
            print(f"Upserted data for {lat}, {lon}")
        except Exception as e:
            print(f"Error for {lat}, {lon}: {e}")

    # Cleanup old data
    cleanup_old_data(conn, days=7)

    conn.close()

if __name__ == "__main__":
    main()

