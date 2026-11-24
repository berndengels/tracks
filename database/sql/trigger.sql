DROP TRIGGER IF EXISTS setPosBeforeInsert;

DELIMITER //

CREATE TRIGGER setPosBeforeInsert
    BEFORE INSERT ON route_planner_data
    FOR EACH ROW
BEGIN
    -- Setzt den Punkt direkt beim Einfügen (ohne extra UPDATE)
    SET NEW.pos = ST_PointFromText(CONCAT('POINT(', NEW.lng, ' ', NEW.lat, ')'), 4326);
END;
//

DELIMITER ;

DROP TRIGGER IF EXISTS setPosBeforeUpdate;

DELIMITER //

CREATE TRIGGER setPosBeforeUpdate
    BEFORE UPDATE ON route_planner_data
    FOR EACH ROW
BEGIN
    -- Setzt den Punkt direkt beim Einfügen (ohne extra UPDATE)
    SET NEW.pos = ST_PointFromText(CONCAT('POINT(', NEW.lng, ' ', NEW.lat, ')'), 4326);
END;
//

DELIMITER ;
