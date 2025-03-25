-- 1. Retrieve top-performing players based on total gold earned
-- This query joins the players and player_statistics tables to show the most financially successful players
-- It displays player name, farm name, and total gold earned, sorted in descending order
-- Limits the result to top 5 players to highlight the most successful farmers
SELECT p.name AS player_name, p.farm_name AS farm_name, ps.total_gold_earned AS total_gold
FROM players p
JOIN player_statistics ps ON p.player_id = ps.player_id
ORDER BY ps.total_gold_earned DESC
LIMIT 5;

-- 2. List all crops that can be planted in each season
-- Groups crops by their planting season and concatenates crop names
-- Helps players understand which crops are available during different seasons
-- Useful for crop rotation and seasonal farming strategy planning
SELECT season, GROUP_CONCAT(name) AS plantable_crops
FROM crops
GROUP BY season;

-- 3. Detailed overview of players' animal ownership and friendship levels
-- Provides comprehensive information about owned animals, including player name, animal name, type, and friendship level
-- Filtered to show only currently owned animals
-- Sorted by player name and friendship level to show most-loved animals
SELECT p.name AS player_name, a.name AS animal_name, at.type AS animal_type, pao.friendship_level
FROM players p
JOIN player_animals_owned pao ON p.player_id = pao.player_id
JOIN animals a ON pao.animal_id = a.animal_id
JOIN animal_types at ON a.type_id = at.type_id
WHERE pao.owned = 1
ORDER BY p.name, pao.friendship_level DESC;

-- 4. Comprehensive mapping of animal types to their produce
-- Uses LEFT JOIN to include animal types even if they don't have associated produce
-- Aggregates produce types for each animal type using GROUP_CONCAT
-- Helps players understand potential resources from different animal types
SELECT at.type AS animal_type, GROUP_CONCAT(ap.produce_type) AS produce_items
FROM animal_types at
LEFT JOIN animal_produce ap ON at.type_id = ap.type_id
GROUP BY at.type;

-- 5. Identify the most successful crop for each player in terms of harvesting
-- Finds the crop with the highest number of harvests for each player
-- Useful for understanding which crops are most productive for individual players
-- Sorted by total harvests in descending order to highlight top-performing crops
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

-- 6. Analyze player engagement through total playtime and session frequency
-- Calculates total playtime in minutes and number of game sessions for each player
-- Helps understand player dedication and gaming habits
-- Sorted by total playtime to identify most active players
SELECT p.name AS player_name, 
       SUM(TIMESTAMPDIFF(MINUTE, gs.start_time, gs.end_time)) AS total_playtime_minutes,
       COUNT(gs.session_id) AS session_count
FROM players p
JOIN game_sessions gs ON p.player_id = gs.player_id
GROUP BY p.player_id
ORDER BY total_playtime_minutes DESC;

-- 7. Track players' achievement progression
-- Counts the number of completed achievements for each player
-- Limited to top 5 to highlight most accomplished players
-- Provides insight into player progression and game completion
SELECT p.name AS player_name, COUNT(pa.achievement_id) AS completed_achievements
FROM players p
JOIN game_sessions gs ON p.player_id = gs.player_id
JOIN player_achievements pa ON gs.session_id = pa.session_id
WHERE pa.status = 'Completed'
GROUP BY p.player_id
ORDER BY completed_achievements DESC
LIMIT 5;

-- 8. Analyze coop animal requirements and incubation details
-- Shows different animal types, their coop size requirements, and incubation times
-- Helps players understand the housing and breeding requirements for different animals
-- Sorted by coop size requirement for easy comparison
SELECT at.type AS animal_type, ca.coop_size_requirement AS required_coop_level, ca.incubate_time AS incubation_days
FROM animal_types at
JOIN animals a ON at.type_id = a.type_id
JOIN coop_animals ca ON a.animal_id = ca.animal_id
GROUP BY at.type, ca.coop_size_requirement
ORDER BY ca.coop_size_requirement;

-- 9. Identify most valuable items in players' inventories
-- Calculates total item value by multiplying unit price and quantity
-- Helps players understand their most valuable resources
-- Limited to top 10 most valuable item collections
SELECT p.name AS player_name, i.name AS item_name, i.type AS item_type, 
       i.value AS unit_price, inv.quantity, (i.value * inv.quantity) AS total_value
FROM players p
JOIN inventory inv ON p.player_id = inv.player_id
JOIN items i ON inv.item_id = i.item_id
ORDER BY total_value DESC
LIMIT 10;

-- 10. Quick database snapshot: record count for all tables
-- Provides a comprehensive overview of data volume in each table
-- Useful for database maintenance and understanding data distribution
-- Helps identify which tables have the most records
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

-- 11. Detailed Seasonal Crop Performance Analysis
-- This query provides a comprehensive overview of crop performance across different seasons
-- It calculates total harvested crops, total sold crops, and the average percentage of crops sold
-- Results are sorted by total harvested amount to highlight the most successful crops
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

-- 12. In-Depth Player Engagement and Achievement Progress Tracking
-- Analyzes the correlation between playtime and achievement completion
-- Calculates total playtime, number of completed achievements, 
-- and the percentage of all achievements completed by each player
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

-- 13. Comprehensive Animal Friendship and Produce Efficiency Analysis
-- Explores the relationship between animal friendship levels and produce production
-- Tracks unique produce items, total produce quantity, and friendship levels for each animal
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

-- 14. Comprehensive Inventory Value Distribution Analysis
-- Provides a detailed breakdown of item values across different item types
-- Calculates unique item count, average/min/max values, and total inventory value per item type
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

-- 15. Detailed Session Length and Player Performance Correlation
-- Investigates the relationship between game session length, 
-- in-game achievements, and gold earning potential
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

-- 16. Comprehensive Multi-Dimensional Player Progression Tracking
-- Provides a holistic view of player progress across various game dimensions
-- Includes total gold earned, game days, achievements, animals, crops, and more
SELECT 
    p.name AS player_name,
    p.farm_name,
    ps.total_gold_earned,
    ps.in_game_days,
    COUNT(DISTINCT pa.achievement_id) AS completed_achievements,
    COUNT(DISTINCT pao.animal_id) AS unique_animals_owned,
    COUNT(DISTINCT pch.crop_id) AS unique_crops_harvested,
    ROUND(ps.total_gold_earned / ps.in_game_days, 2) AS gold_per_day,
    ROUND(COUNT(DISTINCT pa.achievement_id) / (SELECT COUNT(*) FROM achievements) * 100, 2) AS achievement_completion_rate
FROM players p
JOIN player_statistics ps ON p.player_id = ps.player_id
LEFT JOIN player_achievements pa ON p.player_id = (SELECT player_id FROM game_sessions WHERE session_id = pa.session_id) AND pa.status = 'Completed'
LEFT JOIN player_animals_owned pao ON p.player_id = pao.player_id AND pao.owned = 1
LEFT JOIN player_crops_harvested pch ON p.player_id = pch.player_id
GROUP BY p.player_id
ORDER BY ps.total_gold_earned DESC
LIMIT 10;

-- 17. Advanced Item Type Economic Analysis
-- Performs a deep dive into item economics, revealing value, inventory, and economic patterns
-- Uses a Common Table Expression (CTE) to calculate complex economic metrics
WITH ItemEconomics AS (
    SELECT 
        i.type AS item_type,
        COUNT(DISTINCT inv.item_id) AS unique_items,
        ROUND(AVG(i.value), 2) AS avg_item_value,
        ROUND(SUM(i.value * inv.quantity), 2) AS total_type_value,
        ROUND(SUM(inv.quantity), 2) AS total_quantity,
        ROUND(AVG(inv.quantity), 2) AS avg_quantity_per_player
    FROM inventory inv
    JOIN items i ON inv.item_id = i.item_id
    GROUP BY i.type
)
SELECT 
    item_type, 
    unique_items, 
    avg_item_value, 
    total_type_value, 
    total_quantity,
    avg_quantity_per_player,
    ROUND(total_type_value / total_quantity, 2) AS avg_stack_value
FROM ItemEconomics
ORDER BY total_type_value DESC;

-- 18. Advanced Interconnected Animal and Crop Interaction Analysis
-- Explores the complex relationships between animal ownership, crop harvesting, 
-- and overall game progression for each player
SELECT 
    p.name AS player_name,
    COUNT(DISTINCT pao.animal_id) AS total_animals,
    COUNT(DISTINCT at.type) AS unique_animal_types,
    COUNT(DISTINCT pch.crop_id) AS total_crop_varieties,
    ROUND(AVG(pao.friendship_level), 2) AS avg_animal_friendship,
    SUM(pch.harvested) AS total_crops_harvested,
    SUM(pch.sold) AS total_crops_sold,
    ROUND(SUM(pch.sold) / NULLIF(SUM(pch.harvested), 0) * 100, 2) AS sell_percentage,
    ps.in_game_days
FROM players p
LEFT JOIN player_animals_owned pao ON p.player_id = pao.player_id AND pao.owned = 1
LEFT JOIN animals a ON pao.animal_id = a.animal_id
LEFT JOIN animal_types at ON a.type_id = at.type_id
LEFT JOIN player_crops_harvested pch ON p.player_id = pch.player_id
JOIN player_statistics ps ON p.player_id = ps.player_id
GROUP BY p.player_id
ORDER BY total_animals DESC, total_crops_harvested DESC
LIMIT 15;

-- 19. Seasonal Game Progression Patterns Analysis
-- Provides insights into player performance and activity across different game seasons
-- Uses a Common Table Expression (CTE) to calculate detailed seasonal metrics
WITH SeasonalProgress AS (
    SELECT 
        p.player_id,
        p.name AS player_name,
        c.season,
        SUM(pch.harvested) AS total_harvested,
        SUM(pch.sold) AS total_sold,
        COUNT(DISTINCT c.crop_id) AS unique_crops,
        COUNT(DISTINCT gs.session_id) AS season_play_sessions
    FROM players p
    JOIN player_crops_harvested pch ON p.player_id = pch.player_id
    JOIN crops c ON pch.crop_id = c.crop_id
    JOIN game_sessions gs ON p.player_id = gs.player_id
    GROUP BY p.player_id, p.name, c.season
)
SELECT 
    season,
    ROUND(AVG(total_harvested), 2) AS avg_total_harvested,
    ROUND(AVG(total_sold), 2) AS avg_total_sold,
    ROUND(AVG(unique_crops), 2) AS avg_unique_crops_per_player,
    ROUND(AVG(season_play_sessions), 2) AS avg_play_sessions
FROM SeasonalProgress
GROUP BY season
ORDER BY avg_total_harvested DESC;

-- 20. Ultimate Player Performance and Achievement Metrics Compilation
-- Provides the most comprehensive view of player performance
-- Combines multiple game progression metrics into a single, detailed query
SELECT 
    p.name AS player_name,
    p.farm_name,
    ps.total_gold_earned,
    ps.in_game_days,
    ROUND(ps.total_gold_earned / NULLIF(ps.in_game_days, 0), 2) AS gold_per_day,
    COUNT(DISTINCT pa.achievement_id) AS completed_achievements,
    COUNT(DISTINCT pao.animal_id) AS animals_owned,
    COUNT(DISTINCT pch.crop_id) AS crops_harvested,
    ROUND(AVG(pao.friendship_level), 2) AS avg_animal_friendship,
    COUNT(DISTINCT gs.session_id) AS total_game_sessions,
    ROUND(AVG(TIMESTAMPDIFF(MINUTE, gs.start_time, gs.end_time)), 2) AS avg_session_length_minutes
FROM players p
JOIN player_statistics ps ON p.player_id = ps.player_id
LEFT JOIN game_sessions gs ON p.player_id = gs.player_id
LEFT JOIN player_achievements pa ON gs.session_id = pa.session_id AND pa.status = 'Completed'
LEFT JOIN player_animals_owned pao ON p.player_id = pao.player_id AND pao.owned = 1
LEFT JOIN player_crops_harvested pch ON p.player_id = pch.player_id
GROUP BY p.player_id
ORDER BY completed_achievements DESC, total_gold_earned DESC
LIMIT 20;
