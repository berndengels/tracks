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
