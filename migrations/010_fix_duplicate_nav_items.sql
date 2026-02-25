-- Migration 010: Fix duplicate nav_items
-- Remove duplicate nav_items that were re-inserted by migration 004
-- Keep only the original rows (lowest id per title)

DELETE n1 FROM nav_items n1
INNER JOIN nav_items n2
WHERE n1.id > n2.id
  AND n1.title = n2.title
  AND n1.url = n2.url
  AND n1.sort_order = n2.sort_order;
