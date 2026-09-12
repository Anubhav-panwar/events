-- ==============================================================================
-- 5 UNIQUE PREMIUM EVENTS SEED SCRIPT (EVENTS 25 - 29)
-- Target: MySQL 5.7+ / 8.0+ / MariaDB 10.x / cPanel phpMyAdmin
-- Compatible with ON DUPLICATE KEY UPDATE for idempotent execution
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. Insert 5 Unique Events
-- ------------------------------------------------------------------------------
INSERT INTO `events` (
    `id`, `vendor_profile_id`, `title`, `description`, `venue_name`,
    `event_date`, `start_time`, `end_time`, `address`, `city`, `country`,
    `latitude`, `longitude`, `tags`, `audience`, `category_id`, `capacity`,
    `status`, `is_featured`, `event_type`, `base_price`, `slug`,
    `og_meta`, `created_at`, `updated_at`
) VALUES
(
    25, 1, 'Solaris Sunset Rooftop Sessions',
    'An elevated golden-hour music experience featuring world-class melodic house DJs, 360-degree panoramic Los Angeles skyline views, organic artisanal cocktails, and an ambient fire-pit lounge.',
    'Skybar Highview Rooftop Lounge',
    '2026-10-24', '17:00:00', '23:00:00', '8440 Sunset Boulevard', 'Los Angeles', 'United States',
    34.092809, -118.371300, '[\"melodic-house\", \"rooftop\", \"sunset\", \"cocktails\", \"electronic\", \"dj\"]', '[\"music-lovers\", \"couples\", \"young-professionals\"]', 1, 600,
    'published', 1, 'paid', 45, 'solaris-sunset-rooftop-sessions',
    '{\"title\":\"Solaris Sunset Rooftop Sessions\",\"description\":\"An elevated golden-hour music experience featuring world-class melodic house DJs and panoramic skyline views.\"}',
    NOW(), NOW()
),
(
    26, 1, 'Apex Esports Championship Grand Finals',
    'Witness the top 16 international gaming teams clash live on the massive 4K LED arena stage for a $500,000 championship purse. Features pro-player autograph signings, VR test zones, and hardware giveaways.',
    'Moody Center Arena & Esports Colosseum',
    '2026-11-08', '13:00:00', '21:00:00', '2001 Robert Dedman Drive', 'Austin', 'United States',
    30.280900, -97.732400, '[\"esports\", \"gaming\", \"championship\", \"arena\", \"tournament\", \"vr\"]', '[\"gamers\", \"students\", \"tech-enthusiasts\"]', 2, 4500,
    'published', 1, 'paid', 35, 'apex-esports-championship-grand-finals',
    '{\"title\":\"Apex Esports Championship Grand Finals\",\"description\":\"Witness the top 16 international gaming teams clash live on the massive 4K LED arena stage for $500,000.\"}',
    NOW(), NOW()
),
(
    27, 1, 'Mastering Next-Gen Robotics & Autonomous Drones',
    'A high-intensity, hands-on engineering masterclass where attendees build, calibrate, and program AI-powered quadcopters with computer vision obstacle avoidance and autonomous waypoint navigation. Complete hardware kits included.',
    'MIT Innovation Lab & Maker Pavilion',
    '2026-11-18', '10:00:00', '17:00:00', '77 Massachusetts Avenue', 'Boston', 'United States',
    42.359800, -71.092100, '[\"robotics\", \"drones\", \"ai\", \"coding\", \"hardware\", \"masterclass\"]', '[\"engineers\", \"students\", \"developers\", \"makers\"]', 3, 120,
    'published', 1, 'paid', 150, 'mastering-next-gen-robotics-and-autonomous-drones',
    '{\"title\":\"Mastering Next-Gen Robotics & Autonomous Drones\",\"description\":\"Hands-on engineering masterclass where attendees build, calibrate, and program AI-powered quadcopters.\"}',
    NOW(), NOW()
),
(
    28, 1, 'World Street Food & Night Market Carnival',
    'A vibrant cultural culinary adventure featuring 60+ artisan street food stalls from Tokyo, Bangkok, Mexico City, and Istanbul along the scenic riverbank. Enjoy craft cider bars, fire dancers, and live acoustic buskers under carnival festoon lighting.',
    'Southbank Cultural Riverside Promenade',
    '2026-12-12', '16:00:00', '23:30:00', 'Belvedere Road, South Bank', 'London', 'United Kingdom',
    51.506500, -0.117200, '[\"streetfood\", \"night-market\", \"culinary\", \"carnival\", \"foodies\", \"riverside\"]', '[\"food-lovers\", \"families\", \"tourists\", \"community\"]', 4, 3500,
    'published', 1, 'free', 0, 'world-street-food-and-night-market-carnival',
    '{\"title\":\"World Street Food & Night Market Carnival\",\"description\":\"A vibrant cultural culinary adventure featuring 60+ artisan street food stalls, craft cider bars, and fire dancers.\"}',
    NOW(), NOW()
),
(
    29, 1, 'Neon Cyberpunk Indie Film & Synthwave Night',
    'An electric underground cinema celebration showcasing world-premiere independent sci-fi short films paired with a live synthwave and darksynth soundtrack performed with vintage analog synthesizers in an immersive neon lounge.',
    'Shibuya CineCity & Neon Underpass Lounge',
    '2026-12-20', '19:30:00', '02:00:00', '21-1 Udagawacho, Shibuya City', 'Tokyo', 'Japan',
    35.661800, 139.698300, '[\"cyberpunk\", \"synthwave\", \"film-festival\", \"indie\", \"neon\", \"analog-synth\"]', '[\"film-buffs\", \"sci-fi-fans\", \"music-lovers\"]', 1, 400,
    'published', 1, 'paid', 30, 'neon-cyberpunk-indie-film-and-synthwave-night',
    '{\"title\":\"Neon Cyberpunk Indie Film & Synthwave Night\",\"description\":\"Underground cinema celebration showcasing indie sci-fi short films paired with live analog synthwave soundtracks.\"}',
    NOW(), NOW()
)
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `description` = VALUES(`description`),
    `venue_name` = VALUES(`venue_name`),
    `event_date` = VALUES(`event_date`),
    `start_time` = VALUES(`start_time`),
    `end_time` = VALUES(`end_time`),
    `address` = VALUES(`address`),
    `city` = VALUES(`city`),
    `country` = VALUES(`country`),
    `latitude` = VALUES(`latitude`),
    `longitude` = VALUES(`longitude`),
    `tags` = VALUES(`tags`),
    `audience` = VALUES(`audience`),
    `category_id` = VALUES(`category_id`),
    `capacity` = VALUES(`capacity`),
    `status` = VALUES(`status`),
    `is_featured` = VALUES(`is_featured`),
    `event_type` = VALUES(`event_type`),
    `base_price` = VALUES(`base_price`),
    `slug` = VALUES(`slug`),
    `og_meta` = VALUES(`og_meta`),
    `updated_at` = NOW();

-- ------------------------------------------------------------------------------
-- 2. Insert Ticket Types for Events 25 - 29
-- ------------------------------------------------------------------------------
INSERT INTO `ticket_types` (`id`, `event_id`, `name`, `price`, `currency`, `quantity`, `sold`, `created_at`, `updated_at`) VALUES
(60, 25, 'Sunset Golden Hour Pass', 45.00, 'USD', 400, 0, NOW(), NOW()),
(61, 25, 'VIP Firepit Cabana Admission', 120.00, 'USD', 50, 0, NOW(), NOW()),

(62, 26, 'General Arena Floor Pass', 35.00, 'USD', 3000, 0, NOW(), NOW()),
(63, 26, 'Pro Player Meet & Greet VIP', 85.00, 'USD', 250, 0, NOW(), NOW()),

(64, 27, 'Full Workshop & Quadcopter Kit', 150.00, 'USD', 80, 0, NOW(), NOW()),
(65, 27, 'Student Observer Access', 50.00, 'USD', 40, 0, NOW(), NOW()),

(66, 28, 'Free Community Admission', 0.00, 'USD', 3000, 0, NOW(), NOW()),
(67, 28, 'VIP Fast-Track Foodie Passport', 25.00, 'USD', 500, 0, NOW(), NOW()),

(68, 29, 'Screening & Afterparty Pass', 30.00, 'USD', 300, 0, NOW(), NOW()),
(69, 29, 'Director Circle & VIP Booth', 65.00, 'USD', 60, 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    `name` = VALUES(`name`),
    `price` = VALUES(`price`),
    `currency` = VALUES(`currency`),
    `quantity` = VALUES(`quantity`),
    `updated_at` = NOW();

-- ------------------------------------------------------------------------------
-- 3. Insert Media Rows for Events 25 - 29
-- ------------------------------------------------------------------------------
INSERT INTO `event_media` (`id`, `event_id`, `disk`, `path`, `type`, `original_name`, `size`, `created_at`, `updated_at`) VALUES
(70, 25, 'public', 'events/25/sunset_rooftop_dj.jpg', 'image', 'Rooftop DJ Stage', 46838, NOW(), NOW()),
(71, 25, 'public', 'events/25/firepit_lounge_cocktails.jpg', 'image', 'Firepit Lounge', 47470, NOW(), NOW()),
(72, 25, 'public', 'events/25/skyline_night_dancefloor.jpg', 'image', 'Night Dancefloor', 45954, NOW(), NOW()),

(73, 26, 'public', 'events/26/esports_arena_stage.jpg', 'image', 'Grand Finals Arena', 45920, NOW(), NOW()),
(74, 26, 'public', 'events/26/pro_players_booth.jpg', 'image', 'Championship Stage', 44421, NOW(), NOW()),
(75, 26, 'public', 'events/26/trophy_cup_celebration.jpg', 'image', '$500,000 Trophy', 42492, NOW(), NOW()),

(76, 27, 'public', 'events/27/drone_assembly_lab.jpg', 'image', 'Autonomous Drone Lab', 47305, NOW(), NOW()),
(77, 27, 'public', 'events/27/ai_obstacle_flight.jpg', 'image', 'Flight Arena Testing', 45902, NOW(), NOW()),
(78, 27, 'public', 'events/27/robotics_circuit_workbench.jpg', 'image', 'Sensor Calibration', 44271, NOW(), NOW()),

(79, 28, 'public', 'events/28/riverside_night_market.jpg', 'image', 'Riverside Night Carnival', 46043, NOW(), NOW()),
(80, 28, 'public', 'events/28/sizzling_wok_streetfood.jpg', 'image', 'Asian Street Cuisine', 44357, NOW(), NOW()),
(81, 28, 'public', 'events/28/fire_performers_stage.jpg', 'image', 'Carnival Buskers & Live Acoustic', 44197, NOW(), NOW()),

(82, 29, 'public', 'events/29/neon_cinema_auditorium.jpg', 'image', 'Sci-Fi Film Showcase', 46927, NOW(), NOW()),
(83, 29, 'public', 'events/29/analog_synth_performance.jpg', 'image', 'Live Synthwave Soundtracks', 45630, NOW(), NOW()),
(84, 29, 'public', 'events/29/shibuya_underpass_party.jpg', 'image', 'Shibuya Neon Lounge', 44124, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    `disk` = VALUES(`disk`),
    `path` = VALUES(`path`),
    `type` = VALUES(`type`),
    `original_name` = VALUES(`original_name`),
    `size` = VALUES(`size`),
    `updated_at` = NOW();

SET FOREIGN_KEY_CHECKS = 1;
