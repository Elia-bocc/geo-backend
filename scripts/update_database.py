import requests
import psycopg2
from datetime import datetime, timedelta, timezone

API_KEY = ""

# Aiven DB credentials
DB_HOST = ""
DB_PORT = 13578
DB_NAME = "defaultdb"
DB_USER = "avnadmin"
DB_PASS = ""

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

# DB insert (solo INSERT)
def insert_data(conn, lat, lon, data):
    aqi = data['list'][0]['main']['aqi']
    comp = data['list'][0]['components']
    pm2_5 = comp['pm2_5']
    pm10 = comp['pm10']
    no2 = comp['no2']
    co = comp['co']
    o3 = comp['o3']
    timestamp = datetime.fromtimestamp(data['list'][0]['dt'], tz=timezone.utc)

    sql = """
    INSERT INTO "public"."Air Pollution" (geom, pm2_5, pm10, no2, co, o3, aqi, timestamp)
    VALUES (
        ST_SetSRID(ST_MakePoint(%s, %s), 4326),
        %s, %s, %s, %s, %s, %s, %s
    )
    """
    try:
        with conn.cursor() as cur:
            cur.execute(sql, (lon, lat, pm2_5, pm10, no2, co, o3, aqi, timestamp))
        conn.commit()
    except Exception as e:
        print(f"Insert error for {lat}, {lon}: {e}")
        conn.rollback()

# Cleanup all data
def cleanup_all_data(conn):
    sql = 'DELETE FROM "public"."Air Pollution"'
    try:
        with conn.cursor() as cur:
            cur.execute(sql)
        conn.commit()
        print("Deleted all records from Air Pollution")
    except Exception as e:
        print(f"Cleanup error: {e}")
        conn.rollback()


def main():
    conn = psycopg2.connect(
        host=DB_HOST, port=DB_PORT, dbname=DB_NAME,
        user=DB_USER, password=DB_PASS, sslmode="require"
    )

    # Cleanup old data
    cleanup_all_data(conn)
  
    for lat, lon in coordinates:
        try:
            data = get_air_quality_data(lat, lon)
            insert_data(conn, lat, lon, data)
            print(f"Inserted data for {lat}, {lon}")
        except Exception as e:
            print(f"Error for {lat}, {lon}: {e}")
            conn.rollback()

    

    conn.close()

if __name__ == "__main__":
    main()
