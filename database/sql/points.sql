select *
from `track_data`
where stamp % 600 = 0
  and exists (select *
              from `tracks`
              where `track_data`.`track_id` = `tracks`.`id` and `active` = 1 and YEAR(`start`) = 2026)
  and `lat` between 53.735447 and 55.124468
  and `lng` between 9.083513 and 14.083032
order by `datetime` asc;
