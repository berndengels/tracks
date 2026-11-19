UPDATE media m SET track_id=(SELECT id FROM tracks t
     WHERE t.active = 1
         AND (m.created BETWEEN t.start AND t.end)
        OR (m.created_at BETWEEN t.start AND t.end)
);
UPDATE media SET track_id=NULL;
