-- Sample data for achievements table
INSERT INTO `achievements` (`achievement_id`, `name`, `goal`) VALUES
(1, 'Greenhorn', 'Earn 15,000g'),
(2, 'Cowpoke', 'Earn 50,000g'),
(3, 'Homesteader', 'Earn 250,000g'),
(4, 'Millionaire', 'Earn 1,000,000g'),
(5, 'Legend', 'Earn 10,000,000g'),
(6, 'A Complete Collection', 'Complete the museum collection'),
(7, 'A New Friend', 'Reach 5 hearts with a villager'),
(8, 'Best Friends', 'Reach 10 hearts with 5 villagers'),
(9, 'Beloved Farmer', 'Reach 10 hearts with 8 villagers'),
(10, 'Master Angler', 'Catch every fish'),
(11, 'Mother Catch', 'Catch 100 fish'),
(12, 'Fisherman', 'Catch 10 different fish'),
(13, 'Artisan', 'Ship 30 artisan goods'),
(14, 'Craft Master', 'Craft 30 different items'),
(15, 'Sous Chef', 'Cook 10 different recipes'),
(16, 'Gourmet Chef', 'Cook 25 different recipes'),
(17, 'Moving Up', 'Upgrade house once'),
(18, 'Living Large', 'Upgrade house twice'),
(19, 'Full House', 'Upgrade house fully'),
(20, 'Polyculture', 'Ship 15 different crops');

-- Sample data for animal_types table
INSERT INTO `animal_types` (`type_id`, `type`) VALUES
(1, 'Chicken'),
(2, 'Duck'),
(3, 'Rabbit'),
(4, 'Dinosaur'),
(5, 'Cow'),
(6, 'Goat'),
(7, 'Sheep'),
(8, 'Pig'),
(9, 'Ostrich'),
(10, 'Horse'),
(11, 'Void Chicken'),
(12, 'Golden Chicken'),
(13, 'Blue Chicken'),
(14, 'Brown Chicken'),
(15, 'White Chicken'),
(16, 'Brown Cow'),
(17, 'White Cow'),
(18, 'Alpaca'),
(19, 'Llama'),
(20, 'Cat'),
(21, 'Dog');

-- Sample data for animal_produce table
INSERT INTO `animal_produce` (`type_id`, `produce_type`) VALUES
(1, 'Egg'),
(2, 'Duck Egg'),
(3, 'Wool'),
(4, 'Dinosaur Egg'),
(5, 'Milk'),
(6, 'Goat Milk'),
(7, 'Wool'),
(8, 'Truffle'),
(9, 'Ostrich Egg'),
(11, 'Void Egg'),
(12, 'Golden Egg'),
(13, 'Blue Egg'),
(14, 'Brown Egg'),
(15, 'White Egg'),
(16, 'Milk'),
(17, 'Milk'),
(18, 'Alpaca Wool'),
(19, 'Llama Wool');

-- Sample data for animals table
INSERT INTO `animals` (`animal_id`, `name`, `type_id`) VALUES
(1, 'Clucky', 1),
(2, 'Quackers', 2),
(3, 'Fluffy', 3),
(4, 'Rex', 4),
(5, 'Bessie', 5),
(6, 'Nanette', 6),
(7, 'Wooly', 7),
(8, 'Truffles', 8),
(9, 'Big Bird', 9),
(10, 'Spirit', 10),
(11, 'Shadow', 11),
(12, 'Goldie', 12),
(13, 'Azure', 13),
(14, 'Brownie', 14),
(15, 'Snowball', 15),
(16, 'Patches', 16),
(17, 'Daisy', 17),
(18, 'Alpie', 18),
(19, 'Llamaface', 19),
(20, 'Whiskers', 20),
(21, 'Barky', 21),
(22, 'Egbert', 1),
(23, 'Quacky', 2),
(24, 'Thumper', 3),
(25, 'Chompy', 4);

-- Sample data for barn_animals table
INSERT INTO `barn_animals` (`animal_id`, `barn_size_requirement`) VALUES
(5, 1),  -- Cow requires regular barn
(6, 1),  -- Goat requires regular barn
(7, 1),  -- Sheep requires regular barn
(8, 2),  -- Pig requires big barn
(10, 1), -- Horse requires regular barn
(16, 1), -- Brown Cow requires regular barn
(17, 1), -- White Cow requires regular barn
(18, 2), -- Alpaca requires big barn
(19, 2), -- Llama requires big barn
(20, 1), -- Cat doesn't require barn but for sample data
(21, 1); -- Dog doesn't require barn but for sample data

-- Sample data for coop_animals table
INSERT INTO `coop_animals` (`animal_id`, `coop_size_requirement`, `incubate_time`) VALUES
(1, 1, 1),  -- Chicken requires regular coop, 1 day incubation
(2, 2, 2),  -- Duck requires big coop, 2 days incubation
(3, 3, 4),  -- Rabbit requires deluxe coop, 4 days incubation
(4, 3, 12), -- Dinosaur requires deluxe coop, 12 days incubation
(9, 3, 10), -- Ostrich requires deluxe coop, 10 days incubation
(11, 1, 3), -- Void Chicken requires regular coop, 3 days incubation
(12, 3, 5), -- Golden Chicken requires deluxe coop, 5 days incubation
(13, 1, 1), -- Blue Chicken requires regular coop, 1 day incubation
(14, 1, 1), -- Brown Chicken requires regular coop, 1 day incubation
(15, 1, 1), -- White Chicken requires regular coop, 1 day incubation
(22, 1, 1), -- Egbert requires regular coop, 1 day incubation
(23, 2, 2), -- Quacky requires big coop, 2 days incubation
(24, 3, 4), -- Thumper requires deluxe coop, 4 days incubation
(25, 3, 12); -- Chompy requires deluxe coop, 12 days incubation

-- Sample data for crops table
INSERT INTO `crops` (`crop_id`, `name`, `season`) VALUES
(1, 'Parsnip', 'Spring'),
(2, 'Cauliflower', 'Spring'),
(3, 'Potato', 'Spring'),
(4, 'Garlic', 'Spring'),
(5, 'Kale', 'Spring'),
(6, 'Melon', 'Summer'),
(7, 'Tomato', 'Summer'),
(8, 'Blueberry', 'Summer'),
(9, 'Hot Pepper', 'Summer'),
(10, 'Wheat', 'Summer,Fall'),
(11, 'Corn', 'Summer,Fall'),
(12, 'Pumpkin', 'Fall'),
(13, 'Eggplant', 'Fall'),
(14, 'Cranberries', 'Fall'),
(15, 'Amaranth', 'Fall'),
(16, 'Grape', 'Fall'),
(17, 'Coffee Bean', 'Spring,Summer'),
(18, 'Strawberry', 'Spring'),
(19, 'Ancient Fruit', 'Spring,Summer,Fall'),
(20, 'Sweet Gem Berry', 'Fall');

-- Sample data for players table
INSERT INTO `players` (`player_id`, `name`, `avatar`, `farm_name`) VALUES
(1, 'FarmerJohn', 'male_1', 'Sunflower Farm'),
(2, 'ValleyGirl', 'female_1', 'Moonlight Valley'),
(3, 'StarGazer', 'male_2', 'Stardust Field'),
(4, 'CropMaster', 'female_2', 'Harvest Moon'),
(5, 'AnimalWhisperer', 'male_3', 'Creature Comfort'),
(6, 'MineExplorer', 'female_3', 'Ruby Ridge'),
(7, 'FishingPro', 'male_4', 'Ripple Row'),
(8, 'ForageFinder', 'female_4', 'Wild Woods'),
(9, 'CraftKing', 'male_5', 'Artisan Acres'),
(10, 'CookingQueen', 'female_5', 'Gourmet Garden'),
(11, 'JojaMember', 'male_6', 'Corporate Fields'),
(12, 'CommunityHero', 'female_6', 'Pelican Pride'),
(13, 'RanchRuler', 'male_7', 'Livestock Lane'),
(14, 'OrchardOwner', 'female_7', 'Fruit Forest'),
(15, 'WineMaker', 'male_8', 'Vineyard Valley'),
(16, 'BeeKeeper', 'female_8', 'Honey Hollow'),
(17, 'TruffleTreasure', 'male_9', 'Mushroom Meadow'),
(18, 'FlowerFarmer', 'female_9', 'Petal Patch'),
(19, 'CaveCaretaker', 'male_10', 'Crystal Cavern'),
(20, 'GreenhouseGrower', 'female_10', 'Year-Round Ranch');

-- Sample data for player_statistics table
INSERT INTO `player_statistics` (`player_id`, `total_gold_earned`, `in_game_days`) VALUES
(1, 1250000, 112),
(2, 567800, 87),
(3, 2340500, 156),
(4, 4567890, 243),
(5, 987600, 92),
(6, 3456700, 178),
(7, 789500, 65),
(8, 456800, 38),
(9, 1678900, 124),
(10, 2345600, 145),
(11, 567800, 42),
(12, 3459800, 187),
(13, 789600, 76),
(14, 1234500, 102),
(15, 4567800, 198),
(16, 1234567, 114),
(17, 2345678, 165),
(18, 876500, 82),
(19, 1987600, 132),
(20, 3456700, 176);

-- Sample data for items table
INSERT INTO `items` (`item_id`, `name`, `type`, `value`) VALUES
(1, 'Parsnip', 'Vegetable', 35),
(2, 'Cauliflower', 'Vegetable', 175),
(3, 'Potato', 'Vegetable', 80),
(4, 'Garlic', 'Vegetable', 60),
(5, 'Kale', 'Vegetable', 110),
(6, 'Melon', 'Fruit', 250),
(7, 'Tomato', 'Vegetable', 60),
(8, 'Blueberry', 'Fruit', 50),
(9, 'Hot Pepper', 'Vegetable', 40),
(10, 'Wheat', 'Vegetable', 25),
(11, 'Corn', 'Vegetable', 50),
(12, 'Pumpkin', 'Vegetable', 320),
(13, 'Eggplant', 'Vegetable', 60),
(14, 'Cranberries', 'Fruit', 75),
(15, 'Amaranth', 'Vegetable', 150),
(16, 'Grape', 'Fruit', 80),
(17, 'Coffee Bean', 'Seeds', 15),
(18, 'Strawberry', 'Fruit', 120),
(19, 'Ancient Fruit', 'Fruit', 550),
(20, 'Sweet Gem Berry', 'Fruit', 3000),
(21, 'Chicken Egg', 'Animal Product', 50),
(22, 'Duck Egg', 'Animal Product', 95),
(23, 'Wool', 'Animal Product', 340),
(24, 'Dinosaur Egg', 'Animal Product', 350),
(25, 'Milk', 'Animal Product', 125),
(26, 'Goat Milk', 'Animal Product', 225),
(27, 'Truffle', 'Animal Product', 625),
(28, 'Ostrich Egg', 'Animal Product', 600),
(29, 'Mayonnaise', 'Artisan Good', 190),
(30, 'Cheese', 'Artisan Good', 230);

-- Sample data for inventory table
INSERT INTO `inventory` (`inventory_id`, `player_id`, `item_id`, `quantity`) VALUES
(1, 1, 1, 50),
(2, 1, 6, 25),
(3, 1, 12, 15),
(4, 1, 21, 12),
(5, 1, 25, 8),
(6, 2, 2, 30),
(7, 2, 7, 40),
(8, 2, 13, 20),
(9, 2, 22, 10),
(10, 2, 26, 5),
(11, 3, 3, 45),
(12, 3, 8, 60),
(13, 3, 14, 35),
(14, 3, 23, 7),
(15, 3, 27, 3),
(16, 4, 4, 55),
(17, 4, 9, 70),
(18, 4, 15, 25),
(19, 4, 24, 2),
(20, 4, 28, 1),
(21, 5, 5, 40),
(22, 5, 10, 100),
(23, 5, 16, 45),
(24, 5, 29, 15),
(25, 5, 30, 20),
(26, 6, 19, 10),
(27, 6, 20, 5),
(28, 7, 18, 30),
(29, 8, 17, 50),
(30, 9, 11, 65);

-- Sample data for game_sessions table
INSERT INTO `game_sessions` (`session_id`, `player_id`, `start_time`, `end_time`) VALUES
(1, 1, '2025-01-01 10:00:00', '2025-01-01 12:30:00'),
(2, 1, '2025-01-02 15:00:00', '2025-01-02 17:45:00'),
(3, 2, '2025-01-01 18:00:00', '2025-01-01 20:15:00'),
(4, 2, '2025-01-03 20:30:00', '2025-01-03 23:00:00'),
(5, 3, '2025-01-02 09:00:00', '2025-01-02 11:30:00'),
(6, 3, '2025-01-04 14:00:00', '2025-01-04 16:15:00'),
(7, 4, '2025-01-03 16:30:00', '2025-01-03 19:00:00'),
(8, 4, '2025-01-05 10:00:00', '2025-01-05 12:45:00'),
(9, 5, '2025-01-04 13:00:00', '2025-01-04 15:30:00'),
(10, 5, '2025-01-06 19:00:00', '2025-01-06 21:45:00'),
(11, 6, '2025-01-07 10:00:00', '2025-01-07 12:30:00'),
(12, 7, '2025-01-08 14:00:00', '2025-01-08 16:30:00'),
(13, 8, '2025-01-09 17:00:00', '2025-01-09 19:15:00'),
(14, 9, '2025-01-10 20:00:00', '2025-01-10 22:00:00'),
(15, 10, '2025-01-11 13:00:00', '2025-01-11 15:45:00'),
(16, 11, '2025-01-12 11:00:00', '2025-01-12 13:30:00'),
(17, 12, '2025-01-13 16:00:00', '2025-01-13 18:45:00'),
(18, 13, '2025-01-14 19:00:00', '2025-01-14 21:30:00'),
(19, 14, '2025-01-15 10:00:00', '2025-01-15 12:15:00'),
(20, 15, '2025-01-16 14:00:00', '2025-01-16 16:30:00');

-- Sample data for player_achievements table
INSERT INTO `player_achievements` (`achievement_id`, `session_id`, `status`) VALUES
(1, 1, 'Completed'),
(2, 1, 'In Progress'),
(3, 1, 'Not Started'),
(4, 1, 'Not Started'),
(5, 1, 'Not Started'),
(1, 2, 'Completed'),
(2, 2, 'Completed'),
(3, 2, 'In Progress'),
(4, 2, 'Not Started'),
(5, 2, 'Not Started'),
(1, 3, 'Completed'),
(6, 3, 'In Progress'),
(7, 3, 'Completed'),
(8, 3, 'In Progress'),
(9, 3, 'Not Started'),
(10, 4, 'In Progress'),
(11, 4, 'Completed'),
(12, 4, 'Completed'),
(13, 5, 'In Progress'),
(14, 5, 'Completed'),
(15, 6, 'Completed'),
(16, 6, 'In Progress'),
(17, 7, 'Completed'),
(18, 7, 'In Progress'),
(19, 8, 'Not Started'),
(20, 9, 'Completed');

-- Sample data for player_animals_owned table
INSERT INTO `player_animals_owned` (`player_id`, `animal_id`, `friendship_level`, `owned`) VALUES
(1, 1, 8, 1),
(1, 5, 10, 1),
(1, 8, 7, 1),
(2, 2, 9, 1),
(2, 6, 8, 1),
(2, 9, 6, 1),
(3, 3, 10, 1),
(3, 7, 9, 1),
(3, 10, 10, 1),
(4, 4, 5, 1),
(4, 11, 7, 1),
(4, 12, 6, 1),
(5, 13, 8, 1),
(5, 16, 10, 1),
(5, 18, 9, 1),
(6, 14, 7, 1),
(6, 17, 8, 1),
(6, 19, 6, 1),
(7, 15, 9, 1),
(7, 20, 10, 1),
(8, 21, 8, 1),
(8, 22, 7, 1),
(9, 23, 6, 1),
(9, 24, 9, 1),
(10, 25, 8, 1);

-- Sample data for player_crops_harvested table
INSERT INTO `player_crops_harvested` (`player_id`, `crop_id`, `harvested`, `sold`) VALUES
(1, 1, 250, 200),
(1, 6, 175, 150),
(1, 12, 125, 100),
(2, 2, 200, 180),
(2, 7, 220, 190),
(2, 13, 150, 120),
(3, 3, 300, 250),
(3, 8, 400, 350),
(3, 14, 180, 150),
(4, 4, 275, 250),
(4, 9, 325, 300),
(4, 15, 150, 125),
(5, 5, 225, 200),
(5, 10, 350, 300),
(5, 16, 200, 175),
(6, 17, 400, 350),
(6, 18, 300, 250),
(7, 19, 150, 125),
(7, 20, 75, 50),
(8, 1, 180, 150),
(9, 2, 220, 190),
(10, 3, 260, 230),
(11, 4, 240, 210),
(12, 5, 210, 180),
(13, 6, 190, 160),
(14, 7, 230, 200),
(15, 8, 270, 240),
(16, 9, 250, 220),
(17, 10, 290, 260),
(18, 11, 310, 280),
(19, 12, 200, 170),
(20, 13, 170, 140);