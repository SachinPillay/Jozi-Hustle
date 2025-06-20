-- Add sender_id column to chats table
ALTER TABLE `chats` 
ADD COLUMN `sender_id` int(11) DEFAULT NULL AFTER `seller_id`,
ADD CONSTRAINT `chats_ibfk_3` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- Update existing messages: 
-- If message is from buyer, set sender_id = buyer_id
-- If message is from seller, set sender_id = seller_id
UPDATE `chats` SET `sender_id` = `buyer_id`;
