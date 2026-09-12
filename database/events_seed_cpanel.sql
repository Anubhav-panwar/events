-- ==============================================================================
-- CPANEL SEED SQL: 15 COMPREHENSIVE EVENTS WITH FULL GALLERIES & TICKETS
-- Target: MySQL 5.7+ / 8.0+ / MariaDB 10.x
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Music', 'music', NOW(), NOW()),
(2, 'Sports', 'sports', NOW(), NOW()),
(3, 'Workshop', 'workshop', NOW(), NOW()),
(4, 'Food & Drink', 'food-drink', NOW(), NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `slug` = VALUES(`slug`), `updated_at` = NOW();

-- 2. Vendor Profile
INSERT INTO `vendor_profiles` (`id`, `user_id`, `business_name`, `slug`, `is_approved`, `created_at`, `updated_at`) VALUES
(1, 2, 'Acme Events & Productions', 'acme-events', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `is_approved` = 1, `updated_at` = NOW();

-- 3. 15 Comprehensive Events
INSERT INTO `events` (
    `id`, `vendor_profile_id`, `title`, `description`, `venue_name`,
    `event_date`, `start_time`, `end_time`, `address`, `city`, `country`,
    `latitude`, `longitude`, `tags`, `audience`, `category_id`, `capacity`,
    `status`, `is_featured`, `event_type`, `base_price`, `slug`,
    `og_meta`, `created_at`, `updated_at`
) VALUES
(
    10, 1, 'Symphony Under The Stars 2026', 'Experience an enchanting evening of symphonic classics performed by the world-renowned Philharmonic Orchestra under a starlit sky. Includes access to the twilight lawn, reserved tier seating, and artisanal beverage bars.', 'Grand Amphitheater & Symphony Park',
    '2026-10-15', '19:00:00', '22:30:00', '150 Philharmonic Drive', 'New York', 'United States',
    40.712776, -74.005974, '[\"classical\", \"orchestra\", \"live-music\", \"outdoor\", \"symphony\"]', '[\"music-lovers\", \"couples\", \"families\", \"general\"]', 1, 1500,
    'published', 1, 'paid', 65, 'symphony-under-the-stars-2026',
    '{\"title\":\"Symphony Under The Stars 2026\",\"description\":\"Experience an enchanting evening of symphonic classics performed by the world-renowned Philharmonic \"}', NOW(), NOW()
),
(
    11, 1, 'Global AI & Tech Innovators Summit', 'Join 2,000+ AI researchers, founders, and engineers for keynote talks, live prototype demos, and immersive hardware exhibitions showcasing the frontier of artificial intelligence and agentic computing.', 'Silicon Convention & Expo Center',
    '2026-11-20', '09:00:00', '18:00:00', '742 Innovation Way', 'San Francisco', 'United States',
    37.774929, -122.419416, '[\"ai\", \"technology\", \"summit\", \"keynote\", \"networking\", \"founders\"]', '[\"developers\", \"founders\", \"investors\", \"tech-enthusiasts\"]', 3, 2500,
    'published', 1, 'paid', 199, 'global-ai-and-tech-innovators-summit',
    '{\"title\":\"Global AI & Tech Innovators Summit\",\"description\":\"Join 2,000+ AI researchers, founders, and engineers for keynote talks, live prototype demos, and imm\"}', NOW(), NOW()
),
(
    12, 1, 'Artisan Food & Craft Wine Gala', 'An exclusive gourmet journey celebrating artisanal culinary crafts, master sommelier cellar tastings, farm-to-table courses, and live acoustic music on a panoramic glass rooftop terrace.', 'Skyline Harbor Glass Pavilion',
    '2026-10-28', '18:30:00', '22:00:00', '88 Waterfront Boulevard', 'Chicago', 'United States',
    41.878113, -87.629799, '[\"wine\", \"gourmet\", \"dining\", \"chef\", \"tasting\", \"rooftop\"]', '[\"foodies\", \"couples\", \"connoisseurs\"]', 4, 450,
    'published', 1, 'paid', 85, 'artisan-food-and-craft-wine-gala',
    '{\"title\":\"Artisan Food & Craft Wine Gala\",\"description\":\"An exclusive gourmet journey celebrating artisanal culinary crafts, master sommelier cellar tastings\"}', NOW(), NOW()
),
(
    13, 1, 'Metropolis Marathon & Wellness Expo', 'The premier city distance running event featuring full 42K, half 21K, and 5K fun runs along scenic coastal highways. Finishers enjoy complimentary wellness zones, hydro lounges, and celebration concerts.', 'Civic Center Stadium & Waterfront Promenade',
    '2026-12-05', '06:00:00', '14:00:00', '1200 Harborfront Road', 'Seattle', 'United States',
    47.606209, -122.332071, '[\"marathon\", \"fitness\", \"running\", \"wellness\", \"expo\", \"community\"]', '[\"athletes\", \"runners\", \"families\", \"fitness-fans\"]', 2, 5000,
    'published', 1, 'free', 0, 'metropolis-marathon-and-wellness-expo',
    '{\"title\":\"Metropolis Marathon & Wellness Expo\",\"description\":\"The premier city distance running event featuring full 42K, half 21K, and 5K fun runs along scenic c\"}', NOW(), NOW()
),
(
    14, 1, 'Neon Horizon Electronic Music Festival', 'A three-stage immersive electronic music spectacle featuring top international DJs, massive laser arrays, pyrotechnics, and interactive digital art installations under the Miami skyline.', 'Bayfront Park Ultra Arena',
    '2026-11-14', '17:00:00', '03:00:00', '301 Biscayne Boulevard', 'Miami', 'United States',
    25.778135, -80.1859, '[\"electronic\", \"edm\", \"festival\", \"dance\", \"dj\", \"lights\"]', '[\"party-goers\", \"music-lovers\", \"young-adults\"]', 1, 8000,
    'published', 1, 'paid', 120, 'neon-horizon-electronic-music-festival',
    '{\"title\":\"Neon Horizon Electronic Music Festival\",\"description\":\"A three-stage immersive electronic music spectacle featuring top international DJs, massive laser ar\"}', NOW(), NOW()
),
(
    15, 1, 'NextGen Cloud & Cybersecurity Conference', 'Leading security researchers and cloud architects explore zero-trust architecture, AI threat hunting, multi-cloud resilience, and ethical hacking defense strategies.', 'Austin Tech Center & Grand Ballroom',
    '2026-10-22', '08:30:00', '17:30:00', '500 E Cesar Chavez St', 'Austin', 'United States',
    30.26315, -97.74026, '[\"cybersecurity\", \"cloud\", \"devops\", \"enterprise\", \"networking\"]', '[\"security-engineers\", \"ctos\", \"cloud-architects\"]', 3, 1800,
    'published', 0, 'paid', 249, 'nextgen-cloud-and-cybersecurity-conference',
    '{\"title\":\"NextGen Cloud & Cybersecurity Conference\",\"description\":\"Leading security researchers and cloud architects explore zero-trust architecture, AI threat hunting\"}', NOW(), NOW()
),
(
    16, 1, 'Coastal Sunset Jazz & Blues By The Bay', 'Unwind to soulful brass, double bass grooves, and smooth jazz vocals as the Pacific sun sets over San Diego Bay. Features local seafood bites and artisanal wine popups.', 'Embarcadero Marina Park South',
    '2026-11-08', '16:00:00', '21:30:00', '200 Marina Park Way', 'San Diego', 'United States',
    32.70588, -117.16455, '[\"jazz\", \"blues\", \"live-music\", \"sunset\", \"coastal\"]', '[\"jazz-lovers\", \"couples\", \"general\"]', 1, 1200,
    'published', 0, 'paid', 45, 'coastal-sunset-jazz-and-blues-by-the-bay',
    '{\"title\":\"Coastal Sunset Jazz & Blues By The Bay\",\"description\":\"Unwind to soulful brass, double bass grooves, and smooth jazz vocals as the Pacific sun sets over Sa\"}', NOW(), NOW()
),
(
    17, 1, 'International Street Food & Craft Beer Fiesta', 'Over 60 authentic street food vendors from Southeast Asia, Latin America, and Europe pairing delicacies with 40 local microbreweries, craft ciders, and live acoustic music.', 'Tom McCall Waterfront Park',
    '2026-10-18', '11:00:00', '20:00:00', '98 SW Naito Parkway', 'Portland', 'United States',
    45.51737, -122.67382, '[\"food\", \"craft-beer\", \"street-food\", \"festival\", \"family-friendly\"]', '[\"food-lovers\", \"families\", \"beer-enthusiasts\"]', 4, 6000,
    'published', 1, 'free', 0, 'international-street-food-and-craft-beer-fiesta',
    '{\"title\":\"International Street Food & Craft Beer Fiesta\",\"description\":\"Over 60 authentic street food vendors from Southeast Asia, Latin America, and Europe pairing delicac\"}', NOW(), NOW()
),
(
    18, 1, 'Mountain Trail Ultra Challenge & Expo', 'A rugged, scenic trail competition offering 50K, 25K, and 10K courses through alpine ridges. Includes gear expos, navigation workshops, and recovery clinics.', 'Rocky Mountain Vista Amphitheater',
    '2026-11-01', '07:00:00', '17:00:00', '18300 W Alameda Parkway', 'Denver', 'United States',
    39.66538, -105.20579, '[\"trail-running\", \"mountain\", \"fitness\", \"ultra\", \"hiking\"]', '[\"trail-runners\", \"hikers\", \"outdoor-enthusiasts\"]', 2, 1500,
    'published', 0, 'paid', 75, 'mountain-trail-ultra-challenge-and-expo',
    '{\"title\":\"Mountain Trail Ultra Challenge & Expo\",\"description\":\"A rugged, scenic trail competition offering 50K, 25K, and 10K courses through alpine ridges. Include\"}', NOW(), NOW()
),
(
    19, 1, 'UX/UI Design & Creative AI Masterclass', 'An intensive hands-on masterclass for digital product designers. Master AI-accelerated user research, Figma component systems, generative prototyping, and micro-interaction animations.', 'DTLA Arts District Studio Loft',
    '2026-11-28', '10:00:00', '16:30:00', '700 S Santa Fe Ave', 'Los Angeles', 'United States',
    34.03264, -118.23233, '[\"design\", \"ux\", \"ui\", \"ai-tools\", \"figma\", \"masterclass\"]', '[\"designers\", \"product-managers\", \"creatives\"]', 3, 250,
    'published', 0, 'paid', 150, 'ux-ui-design-and-creative-ai-masterclass',
    '{\"title\":\"UX\\/UI Design & Creative AI Masterclass\",\"description\":\"An intensive hands-on masterclass for digital product designers. Master AI-accelerated user research\"}', NOW(), NOW()
),
(
    20, 1, 'Twilight Rooftop Mixology & Jazz Soiree', 'Award-winning master bartenders present smoked botanical cocktails, bourbon flights, and artisan charcuterie pairings with sultry acoustic jazz overlooking Boston Harbor.', 'Seaport View Rooftop Deck',
    '2026-10-30', '19:00:00', '23:30:00', '100 Northern Avenue', 'Boston', 'United States',
    42.35249, -71.04576, '[\"cocktails\", \"mixology\", \"rooftop\", \"jazz\", \"nightlife\"]', '[\"couples\", \"cocktail-lovers\", \"young-professionals\"]', 4, 300,
    'published', 0, 'paid', 55, 'twilight-rooftop-mixology-and-jazz-soiree',
    '{\"title\":\"Twilight Rooftop Mixology & Jazz Soiree\",\"description\":\"Award-winning master bartenders present smoked botanical cocktails, bourbon flights, and artisan cha\"}', NOW(), NOW()
),
(
    21, 1, 'Grand Slam Beach Volleyball Open 2026', 'World tour doubles tournament featuring Olympic athletes battling on golden sands against the backdrop of Diamond Head. Includes public skill courts and food trucks.', 'Waikiki Beach Arena & Courts',
    '2026-11-22', '08:00:00', '18:00:00', '2401 Kalakaua Avenue', 'Honolulu', 'United States',
    21.27648, -157.82721, '[\"volleyball\", \"beach\", \"sports\", \"hawaii\", \"tournament\"]', '[\"sports-fans\", \"families\", \"tourists\"]', 2, 3500,
    'published', 0, 'free', 0, 'grand-slam-beach-volleyball-open-2026',
    '{\"title\":\"Grand Slam Beach Volleyball Open 2026\",\"description\":\"World tour doubles tournament featuring Olympic athletes battling on golden sands against the backdr\"}', NOW(), NOW()
),
(
    22, 1, 'Indie Rock Revolution Showcase', 'Five electrifying independent rock and alternative bands take the stage for a high-energy night of distorted guitars, driving drums, and raw lyrical anthems.', 'Broadway Underground Live Hall',
    '2026-11-06', '19:30:00', '01:00:00', '410 Broadway', 'Nashville', 'United States',
    36.16098, -86.77749, '[\"indie-rock\", \"concert\", \"live-music\", \"guitar\", \"nashville\"]', '[\"rock-fans\", \"music-lovers\", \"young-adults\"]', 1, 900,
    'published', 0, 'paid', 38, 'indie-rock-revolution-showcase',
    '{\"title\":\"Indie Rock Revolution Showcase\",\"description\":\"Five electrifying independent rock and alternative bands take the stage for a high-energy night of d\"}', NOW(), NOW()
),
(
    23, 1, 'Startup Pitch Battle & Venture Expo', 'Thirty seed-stage startups compete before Silicon Valley venture capital partners for $500,000 in non-dilutive prizes and syndicate investment opportunities.', 'San Jose Civic Convention Pavilion',
    '2026-12-02', '09:00:00', '17:00:00', '135 W San Carlos St', 'San Jose', 'United States',
    37.33125, -121.89069, '[\"startups\", \"pitch\", \"venture-capital\", \"investors\", \"founders\"]', '[\"entrepreneurs\", \"angels\", \"vc-funds\", \"innovators\"]', 3, 1400,
    'published', 0, 'paid', 89, 'startup-pitch-battle-and-venture-expo',
    '{\"title\":\"Startup Pitch Battle & Venture Expo\",\"description\":\"Thirty seed-stage startups compete before Silicon Valley venture capital partners for $500,000 in no\"}', NOW(), NOW()
),
(
    24, 1, 'Gourmet Chocolate & Specialty Coffee Expo', 'Immerse your senses in bean-to-bar single-origin chocolates and world-class pour-overs. Features barista latte art throwdowns, roasting workshops, and tasting flights.', 'Pacific Market Atrium Hall',
    '2026-11-25', '10:00:00', '18:00:00', '1501 4th Avenue', 'Seattle', 'United States',
    47.61053, -122.33706, '[\"coffee\", \"chocolate\", \"artisan\", \"roasting\", \"barista\"]', '[\"coffee-lovers\", \"foodies\", \"confectionery-fans\"]', 4, 2000,
    'published', 1, 'paid', 30, 'gourmet-chocolate-and-specialty-coffee-expo',
    '{\"title\":\"Gourmet Chocolate & Specialty Coffee Expo\",\"description\":\"Immerse your senses in bean-to-bar single-origin chocolates and world-class pour-overs. Features bar\"}', NOW(), NOW()
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
    `tags` = VALUES(`tags`),
    `audience` = VALUES(`audience`),
    `category_id` = VALUES(`category_id`),
    `capacity` = VALUES(`capacity`),
    `status` = VALUES(`status`),
    `is_featured` = VALUES(`is_featured`),
    `event_type` = VALUES(`event_type`),
    `base_price` = VALUES(`base_price`),
    `slug` = VALUES(`slug`),
    `updated_at` = NOW();

-- 4. Ticket Types for 15 Events
DELETE FROM `ticket_types` WHERE `event_id` IN (10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24);

INSERT INTO `ticket_types` (`id`, `event_id`, `name`, `price`, `currency`, `quantity`, `sold`, `created_at`, `updated_at`) VALUES
(100, 10, 'General Lawn Admission', 65, 'USD', 1000, 124, NOW(), NOW()),
(101, 10, 'VIP Orchestral Pavilion', 140, 'USD', 200, 89, NOW(), NOW()),
(102, 11, 'General Conference Pass', 199, 'USD', 1500, 312, NOW(), NOW()),
(103, 11, 'VIP Founder Lounge Pass', 499, 'USD', 300, 145, NOW(), NOW()),
(104, 12, 'Standard Tasting Pass', 85, 'USD', 300, 78, NOW(), NOW()),
(105, 12, 'Sommelier Cellar Experience', 165, 'USD', 100, 62, NOW(), NOW()),
(106, 13, 'Community Runner Entry (Free)', 0, 'USD', 3000, 940, NOW(), NOW()),
(107, 13, 'Pro Chip-Timed Bib', 35, 'USD', 1000, 480, NOW(), NOW()),
(108, 14, 'General Admission Pass', 120, 'USD', 5000, 2100, NOW(), NOW()),
(109, 14, 'VIP Elevated Deck Pass', 260, 'USD', 800, 450, NOW(), NOW()),
(110, 15, 'Standard Conference Pass', 249, 'USD', 1200, 480, NOW(), NOW()),
(111, 15, 'Hands-on Red Team Workshop', 499, 'USD', 150, 110, NOW(), NOW()),
(112, 16, 'Lawn Seating Admission', 45, 'USD', 800, 290, NOW(), NOW()),
(113, 16, 'Reserved Waterfront Table', 95, 'USD', 150, 95, NOW(), NOW()),
(114, 17, 'General Admission (Free)', 0, 'USD', 4500, 1800, NOW(), NOW()),
(115, 17, 'Craft Beer Tasting Flight (8 Samples)', 28, 'USD', 800, 540, NOW(), NOW()),
(116, 18, '25K Trail Entry Bib', 75, 'USD', 600, 310, NOW(), NOW()),
(117, 18, '50K Ultra Mountain Pass', 110, 'USD', 300, 220, NOW(), NOW()),
(118, 19, 'Studio Participant Seat', 150, 'USD', 180, 95, NOW(), NOW()),
(119, 19, 'VIP Portfolio Review Ticket', 275, 'USD', 40, 32, NOW(), NOW()),
(120, 20, 'Admission + 2 Cocktail Tokens', 55, 'USD', 200, 110, NOW(), NOW()),
(121, 20, 'Mixologist Master Table', 115, 'USD', 50, 38, NOW(), NOW()),
(122, 21, 'Beachside Spectator (Free)', 0, 'USD', 2500, 900, NOW(), NOW()),
(123, 21, 'Center Court Shaded Bleacher', 25, 'USD', 600, 380, NOW(), NOW()),
(124, 22, 'General Admission Pit', 38, 'USD', 650, 310, NOW(), NOW()),
(125, 22, 'Mezzanine Seating + Poster', 65, 'USD', 150, 88, NOW(), NOW()),
(126, 23, 'Attendee & Expo Ticket', 89, 'USD', 1000, 410, NOW(), NOW()),
(127, 23, 'Founder Demo Table + 2 Passes', 350, 'USD', 60, 45, NOW(), NOW()),
(128, 24, 'Tasting Pass + Cup', 30, 'USD', 1400, 620, NOW(), NOW()),
(129, 24, 'VIP Barista Masterclass & Tasting', 75, 'USD', 100, 72, NOW(), NOW());

-- 5. Gallery Images for 15 Events
DELETE FROM `event_media` WHERE `event_id` IN (10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24);

INSERT INTO `event_media` (`id`, `event_id`, `disk`, `path`, `type`, `original_name`, `size`, `created_at`, `updated_at`) VALUES
(10, 10, 'public', 'events/10/orchestra_main_stage.jpg', 'image', 'Main Orchestra Stage', 60402, NOW(), NOW()),
(11, 10, 'public', 'events/10/vip_terrace_view.jpg', 'image', 'VIP Skyline Terrace', 59932, NOW(), NOW()),
(12, 10, 'public', 'events/10/solo_violin_performance.jpg', 'image', 'Lead Violin Soloist', 59257, NOW(), NOW()),
(13, 10, 'public', 'events/10/grand_finale_fireworks.jpg', 'image', 'Symphonic Finale Fireworks', 60108, NOW(), NOW()),
(14, 11, 'public', 'events/11/keynote_auditorium.jpg', 'image', 'Opening Keynote Hall', 61262, NOW(), NOW()),
(15, 11, 'public', 'events/11/robotics_expo_floor.jpg', 'image', 'Robotics Expo Floor', 60970, NOW(), NOW()),
(16, 11, 'public', 'events/11/networking_lounge.jpg', 'image', 'Executive Founders Lounge', 61484, NOW(), NOW()),
(17, 11, 'public', 'events/11/hackathon_arena.jpg', 'image', '48-Hour Live Hackathon', 60827, NOW(), NOW()),
(18, 12, 'public', 'events/12/chef_tasting_table.jpg', 'image', 'Master Chef Tasting Table', 65313, NOW(), NOW()),
(19, 12, 'public', 'events/12/sommelier_wine_cellar.jpg', 'image', 'Sommelier Reserve Cellar', 65183, NOW(), NOW()),
(20, 12, 'public', 'events/12/rooftop_sunset_dining.jpg', 'image', 'Sunset Terrace Dining', 64947, NOW(), NOW()),
(21, 12, 'public', 'events/12/patisserie_dessert_display.jpg', 'image', 'Artisan Pastry & Sweets Gallery', 66597, NOW(), NOW()),
(22, 13, 'public', 'events/13/marathon_start_line.jpg', 'image', 'The Dawn Starting Line', 62440, NOW(), NOW()),
(23, 13, 'public', 'events/13/scenic_bridge_course.jpg', 'image', 'Harbor Bridge Scenic Route', 64354, NOW(), NOW()),
(24, 13, 'public', 'events/13/wellness_recovery_zone.jpg', 'image', 'Athletic Recovery & Hydro Lounge', 65125, NOW(), NOW()),
(25, 13, 'public', 'events/13/finish_line_celebration.jpg', 'image', 'Finish Festival & Medals', 63812, NOW(), NOW()),
(26, 14, 'public', 'events/14/main_stage_lasers.jpg', 'image', 'Main Stage Laser Show', 60871, NOW(), NOW()),
(27, 14, 'public', 'events/14/dj_booth_crowd.jpg', 'image', 'Headliner Performance', 60182, NOW(), NOW()),
(28, 14, 'public', 'events/14/interactive_light_tunnel.jpg', 'image', 'Interactive Glow Tunnel', 59643, NOW(), NOW()),
(29, 15, 'public', 'events/15/cyber_defense_panel.jpg', 'image', 'Zero-Trust Defense Keynote', 67158, NOW(), NOW()),
(30, 15, 'public', 'events/15/hands_on_hacking_lab.jpg', 'image', 'Live Penetration Testing Lab', 68256, NOW(), NOW()),
(31, 15, 'public', 'events/15/cloud_expo_networking.jpg', 'image', 'Enterprise Solutions Pavilion', 67100, NOW(), NOW()),
(32, 16, 'public', 'events/16/sax_solo_sunset.jpg', 'image', 'Sunset Saxophone Solo', 65322, NOW(), NOW()),
(33, 16, 'public', 'events/16/jazz_quartet_stage.jpg', 'image', 'The Blue Harbor Quartet', 66225, NOW(), NOW()),
(34, 16, 'public', 'events/16/waterfront_audience.jpg', 'image', 'Waterfront Dining & Music', 66445, NOW(), NOW()),
(35, 17, 'public', 'events/17/street_food_smoke.jpg', 'image', 'Artisanal Street Skewers', 67007, NOW(), NOW()),
(36, 17, 'public', 'events/17/craft_beer_taps.jpg', 'image', 'Microbrewery Beer Tap Wall', 67029, NOW(), NOW()),
(37, 17, 'public', 'events/17/festival_crowd_market.jpg', 'image', 'Bustling Food Alley', 66990, NOW(), NOW()),
(38, 18, 'public', 'events/18/trail_ridge_runner.jpg', 'image', 'Alpine Ridge Ascent', 62598, NOW(), NOW()),
(39, 18, 'public', 'events/18/aid_station_mountain.jpg', 'image', 'Mountain Pass Aid Station', 64064, NOW(), NOW()),
(40, 18, 'public', 'events/18/outdoor_gear_expo.jpg', 'image', 'Trail Gear Showcase', 62969, NOW(), NOW()),
(41, 19, 'public', 'events/19/design_critique_screen.jpg', 'image', 'Interactive UI Prototyping', 64747, NOW(), NOW()),
(42, 19, 'public', 'events/19/designer_wireframing.jpg', 'image', 'Component Architecture Lab', 65188, NOW(), NOW()),
(43, 19, 'public', 'events/19/creatives_networking.jpg', 'image', 'Design Leaders Mixer', 64944, NOW(), NOW()),
(44, 20, 'public', 'events/20/smoked_cocktail_glass.jpg', 'image', 'Artisanal Smoked Old Fashioned', 66392, NOW(), NOW()),
(45, 20, 'public', 'events/20/rooftop_boston_skyline.jpg', 'image', 'Panoramic Harbor Views', 65437, NOW(), NOW()),
(46, 20, 'public', 'events/20/jazz_trio_brass.jpg', 'image', 'Live Acoustic Trio', 63931, NOW(), NOW()),
(47, 21, 'public', 'events/21/volleyball_spike_sand.jpg', 'image', 'Championship Match Spike', 68490, NOW(), NOW()),
(48, 21, 'public', 'events/21/waikiki_court_overview.jpg', 'image', 'Oceanfront Arena Courts', 68967, NOW(), NOW()),
(49, 21, 'public', 'events/21/medal_podium_beach.jpg', 'image', 'Trophy & Lei Ceremony', 69005, NOW(), NOW()),
(50, 22, 'public', 'events/22/lead_guitarist_jump.jpg', 'image', 'Electrifying Guitar Solo', 65069, NOW(), NOW()),
(51, 22, 'public', 'events/22/packed_crowd_hands.jpg', 'image', 'Roaring Indie Crowd', 64860, NOW(), NOW()),
(52, 22, 'public', 'events/22/backstage_band_lounge.jpg', 'image', 'Band Signing & Vinyl Lounge', 65531, NOW(), NOW()),
(53, 23, 'public', 'events/23/founder_pitch_deck.jpg', 'image', 'Stage Pitch Presentation', 62036, NOW(), NOW()),
(54, 23, 'public', 'events/23/vc_judges_panel.jpg', 'image', 'Investor Panel Q&A', 61042, NOW(), NOW()),
(55, 23, 'public', 'events/23/demo_booth_networking.jpg', 'image', 'Interactive Demo Floor', 61353, NOW(), NOW()),
(56, 24, 'public', 'events/24/latte_art_pour.jpg', 'image', 'Barista Latte Art Throwdown', 63384, NOW(), NOW()),
(57, 24, 'public', 'events/24/dark_chocolate_truffles.jpg', 'image', 'Single-Origin Cocoa Truffles', 64156, NOW(), NOW()),
(58, 24, 'public', 'events/24/coffee_roasting_drum.jpg', 'image', 'Specialty Roasting Demo', 63373, NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;
