-- 1. Query the top 5 players with the most gold
SELECT p.name AS player_name, p.farm_name AS farm_name, ps.total_gold_earned AS total_gold
FROM players p
JOIN player_statistics ps ON p.player_id = ps.player_id
ORDER BY ps.total_gold_earned DESC
LIMIT 5;

-- 2. Query crops that can be planted in each season
SELECT season, GROUP_CONCAT(name) AS plantable_crops
FROM crops
GROUP BY season;

-- 3. Query animals owned by players and their friendship levels
SELECT p.name AS player_name, a.name AS animal_name, at.type AS animal_type, pao.friendship_level
FROM players p
JOIN player_animals_owned pao ON p.player_id = pao.player_id
JOIN animals a ON pao.animal_id = a.animal_id
JOIN animal_types at ON a.type_id = at.type_id
WHERE pao.owned = 1
ORDER BY p.name, pao.friendship_level DESC;

-- 4. Query each animal type and its produce
SELECT at.type AS animal_type, GROUP_CONCAT(ap.produce_type) AS produce_items
FROM animal_types at
LEFT JOIN animal_produce ap ON at.type_id = ap.type_id
GROUP BY at.type;

-- 5. Query the most harvested and sold crop for each player
SELECT p.name AS player_name, c.name AS crop_name, pch.harvested, pch.sold
FROM players p
JOIN player_crops_harvested pch ON p.player_id = pch.player_id
JOIN crops c ON pch.crop_id = c.crop_id
WHERE (p.player_id, pch.harvested) IN (
    SELECT player_id, MAX(harvested)
    FROM player_crops_harvested
    GROUP BY player_id
)
ORDER BY pch.harvested DESC;

-- 6. Query playtime duration for each player
SELECT p.name AS player_name, 
       SUM(TIMESTAMPDIFF(MINUTE, gs.start_time, gs.end_time)) AS total_playtime_minutes,
       COUNT(gs.session_id) AS session_count
FROM players p
JOIN game_sessions gs ON p.player_id = gs.player_id
GROUP BY p.player_id
ORDER BY total_playtime_minutes DESC;

-- 7. Query players with the most completed achievements
SELECT p.name AS player_name, COUNT(pa.achievement_id) AS completed_achievements
FROM players p
JOIN game_sessions gs ON p.player_id = gs.player_id
JOIN player_achievements pa ON gs.session_id = pa.session_id
WHERE pa.status = 'Completed'
GROUP BY p.player_id
ORDER BY completed_achievements DESC
LIMIT 5;

-- 8. Query animals requiring different coop sizes
SELECT at.type AS animal_type, ca.coop_size_requirement AS required_coop_level, ca.incubate_time AS incubation_days
FROM animal_types at
JOIN animals a ON at.type_id = a.type_id
JOIN coop_animals ca ON a.animal_id = ca.animal_id
GROUP BY at.type, ca.coop_size_requirement
ORDER BY ca.coop_size_requirement;

-- 9. Query the most valuable items in players' inventories
SELECT p.name AS player_name, i.name AS item_name, i.type AS item_type, 
       i.value AS unit_price, inv.quantity, (i.value * inv.quantity) AS total_value
FROM players p
JOIN inventory inv ON p.player_id = inv.player_id
JOIN items i ON inv.item_id = i.item_id
ORDER BY total_value DESC
LIMIT 10;

-- 10. Query the record count for all tables in the database
SELECT 'achievements' AS table_name, COUNT(*) AS record_count FROM achievements UNION
SELECT 'animal_produce', COUNT(*) FROM animal_produce UNION
SELECT 'animal_types', COUNT(*) FROM animal_types UNION
SELECT 'animals', COUNT(*) FROM animals UNION
SELECT 'barn_animals', COUNT(*) FROM barn_animals UNION
SELECT 'coop_animals', COUNT(*) FROM coop_animals UNION
SELECT 'crops', COUNT(*) FROM crops UNION
SELECT 'game_sessions', COUNT(*) FROM game_sessions UNION
SELECT 'inventory', COUNT(*) FROM inventory UNION
SELECT 'items', COUNT(*) FROM items UNION
SELECT 'player_achievements', COUNT(*) FROM player_achievements UNION
SELECT 'player_animals_owned', COUNT(*) FROM player_animals_owned UNION
SELECT 'player_crops_harvested', COUNT(*) FROM player_crops_harvested UNION
SELECT 'player_statistics', COUNT(*) FROM player_statistics UNION
SELECT 'players', COUNT(*) FROM players
ORDER BY table_name;

-- 11. Seasonal Crop Performance Analysis
-- Calculate total harvested and sold crops per season
SELECT 
    c.season, 
    c.name AS crop_name, 
    SUM(pch.harvested) AS total_harvested, 
    SUM(pch.sold) AS total_sold,
    AVG(pch.sold / pch.harvested * 100) AS avg_sell_percentage
FROM crops c
JOIN player_crops_harvested pch ON c.crop_id = pch.crop_id
GROUP BY c.season, c.name
ORDER BY total_harvested DESC;

-- 12. Player Engagement and Achievement Progression
-- Analyze the relationship between playtime and achievement completion
SELECT 
    p.name AS player_name, 
    ps.in_game_days,
    SUM(TIMESTAMPDIFF(MINUTE, gs.start_time, gs.end_time)) AS total_playtime_minutes,
    COUNT(DISTINCT pa.achievement_id) AS completed_achievements,
    ROUND(COUNT(DISTINCT pa.achievement_id) / (SELECT COUNT(*) FROM achievements) * 100, 2) AS achievement_completion_percentage
FROM players p
JOIN game_sessions gs ON p.player_id = gs.player_id
JOIN player_statistics ps ON p.player_id = ps.player_id
LEFT JOIN player_achievements pa ON gs.session_id = pa.session_id AND pa.status = 'Completed'
GROUP BY p.player_id
ORDER BY completed_achievements DESC;

-- 13. Animal Friendship and Produce Efficiency
-- Analyze the relationship between animal friendship levels and produce quantity
SELECT 
    p.name AS player_name, 
    at.type AS animal_type, 
    a.name AS animal_name,
    pao.friendship_level,
    ap.produce_type,
    COUNT(DISTINCT inv.item_id) AS unique_produce_items,
    SUM(inv.quantity) AS total_produce_quantity
FROM players p
JOIN player_animals_owned pao ON p.player_id = pao.player_id
JOIN animals a ON pao.animal_id = a.animal_id
JOIN animal_types at ON a.type_id = at.type_id
LEFT JOIN animal_produce ap ON at.type_id = ap.type_id
LEFT JOIN inventory inv ON p.player_id = inv.player_id
JOIN items i ON inv.item_id = i.item_id AND i.name = ap.produce_type
WHERE pao.owned = 1
GROUP BY p.name, at.type, a.name, pao.friendship_level
ORDER BY pao.friendship_level DESC
LIMIT 20;

-- 14. Inventory Value Distribution
-- Analyze the distribution of item values in players' inventories
SELECT 
    i.type AS item_type,
    COUNT(DISTINCT inv.item_id) AS unique_item_count,
    ROUND(AVG(i.value), 2) AS average_item_value,
    ROUND(MIN(i.value), 2) AS min_item_value,
    ROUND(MAX(i.value), 2) AS max_item_value,
    ROUND(SUM(i.value * inv.quantity), 2) AS total_inventory_value
FROM inventory inv
JOIN items i ON inv.item_id = i.item_id
GROUP BY i.type
ORDER BY total_inventory_value DESC;

-- 15. Session Length and Player Performance Correlation
-- Investigate how session length relates to in-game achievements and gold earning
SELECT 
    p.name AS player_name,
    ROUND(AVG(TIMESTAMPDIFF(MINUTE, gs.start_time, gs.end_time)), 2) AS avg_session_length_minutes,
    ps.total_gold_earned,
    ps.in_game_days,
    COUNT(DISTINCT pa.achievement_id) AS completed_achievements,
    ROUND(ps.total_gold_earned / ps.in_game_days, 2) AS gold_per_day
FROM players p
JOIN game_sessions gs ON p.player_id = gs.player_id
JOIN player_statistics ps ON p.player_id = ps.player_id
LEFT JOIN player_achievements pa ON gs.session_id = pa.session_id AND pa.status = 'Completed'
GROUP BY p.player_id
ORDER BY avg_session_length_minutes DESC;