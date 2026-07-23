ALTER TABLE media
    ADD COLUMN pos POINT NOT NULL
    GENERATED ALWAYS AS (POINT(lng, lat)) STORED;

ALTER TABLE `media` CHANGE `pos` `pos` POINT NOT NULL;

ALTER TABLE track_data
    ADD COLUMN pos POINT
    GENERATED ALWAYS AS (POINT(lng, lat)) STORED;

ALTER TABLE `track_data` CHANGE `pos` `pos` POINT NOT NULL;

CREATE SPATIAL INDEX idx_tracking_pos ON media(pos);
CREATE SPATIAL INDEX idx_tracking_pos ON track_data(pos);
