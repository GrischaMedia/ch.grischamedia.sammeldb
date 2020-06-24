DROP TABLE IF EXISTS wcf1_sammel;
CREATE TABLE wcf1_sammel (
	sammelID			INT(10) AUTO_INCREMENT PRIMARY KEY,
	
	isDisabled			TINYINT(1) NOT NULL DEFAULT 0,
	hasLabels			TINYINT(1) NOT NULL DEFAULT 0,
	categoryID			INT(10),
	
	title				VARCHAR(80) NOT NULL,
	details				TEXT,
	number				VARCHAR(192) NOT NULL,
	online				TINYINT(1) NOT NULL DEFAULT 0,
	url					TEXT,
	
	time				INT(10) NOT NULL DEFAULT 0,
	userID				INT(10),
	
	iconExtension		VARCHAR(255) NOT NULL DEFAULT '',
	iconHash			VARCHAR(40) NOT NULL DEFAULT '',
	iconPath			VARCHAR(255) NOT NULL DEFAULT '',
	
	KEY (isDisabled),
	KEY (online),
	KEY (time)
);

ALTER TABLE wcf1_sammel ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wcf1_sammel ADD FOREIGN KEY (categoryID) REFERENCES wcf1_category (categoryID) ON DELETE SET NULL;
