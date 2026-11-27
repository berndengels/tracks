-- track data mit zeitsprüngen
SELECT
    td.id,
    t.name AS trackName,
    td.`datetime` zeit,
    td.stamp AS aktueller_stamp,
    td.prev_stamp AS vorheriger_stamp,
    (td.stamp - td.prev_stamp) AS differenz_sekunden
FROM (
         SELECT
             id,
             track_id,
             `datetime`,
             stamp,
             LAG(stamp) OVER (PARTITION BY track_id ORDER BY stamp ASC) AS prev_stamp
         FROM track_data
     ) AS td
JOIN tracks t ON t.id = td.track_id
WHERE (td.stamp - td.prev_stamp) > 60
ORDER BY differenz_sekunden DESC;

-- distance of item to previos pos als procedure
DELIMITER //

CREATE OR REPLACE PROCEDURE `get_route_calculations`(
    IN `p_route_planner_id` INT UNSIGNED,
    IN `p_speed_kn` DECIMAL(5,2),
    IN `p_departure_time` DATETIME
)
BEGIN
    -- Fallback auf 5.0 Knoten, falls NULL oder <= 0 übergeben wird
    DECLARE v_speed DECIMAL(5,2);
-- Fallback auf aktuelle Uhrzeit (NOW()), falls NULL übergeben wird
DECLARE v_departure DATETIME;

SET v_speed = IF(p_speed_kn IS NULL OR p_speed_kn <= 0, 5.0, p_speed_kn);
SET v_departure = IFNULL(p_departure_time, NOW());

WITH RouteLegs AS (
    SELECT
        id,
        route_planner_id,
        name,
        `desc`,
        lat,
        lng,
        -- Etappendistanz in Metern
        COALESCE(
                ST_Distance_Sphere(
                        pos,
                        LAG(pos) OVER (PARTITION BY route_planner_id ORDER BY id ASC)
                ),
                0
        ) AS leg_meters
    FROM
        route_planner_data
    WHERE
            route_planner_id = p_route_planner_id
),
     RouteTotals AS (
         SELECT
             id,
             route_planner_id,
             name,
             `desc`,
             lat,
             lng,
             leg_meters,
             -- Kumulierte Gesamtdistanz in Metern
             SUM(leg_meters) OVER (ORDER BY id ASC) AS total_meters
         FROM
             RouteLegs
     )
SELECT
    id,
    route_planner_id,
    name,
    `desc`,
    lat,
    lng,
    v_speed AS used_speed_kn,

    -- Distanzen in Seemeilen (nm)
    ROUND(leg_meters / 1852, 2) AS leg_nm,
    ROUND(total_meters / 1852, 2) AS total_nm,

    -- Fahrzeit pro Etappe & Gesamt (Format: HH:MM:SS)
    SEC_TO_TIME(ROUND(((leg_meters / 1852) / v_speed) * 3600)) AS leg_duration,
    SEC_TO_TIME(ROUND(((total_meters / 1852) / v_speed) * 3600)) AS total_duration,

    -- Konkretes Ankunftsdatum & Uhrzeit (ETA) pro Wegpunkt
    DATE_ADD(
            v_departure,
            INTERVAL ROUND(((total_meters / 1852) / v_speed) * 3600) SECOND
    ) AS eta
FROM
    RouteTotals
ORDER BY
    id ASC;
END //

DELIMITER ;

