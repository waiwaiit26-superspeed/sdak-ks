-- Add event_date field for activities (date/time of the actual event)
ALTER TABLE activities
ADD COLUMN event_date DATETIME NULL AFTER end_date;
