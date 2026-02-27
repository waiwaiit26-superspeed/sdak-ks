-- Add show_registrations flag to activities
-- When enabled, logged-in members can see the participant list on the activity detail page
ALTER TABLE activities ADD COLUMN show_registrations TINYINT(1) NOT NULL DEFAULT 0 AFTER access_code;
