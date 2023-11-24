CREATE TABLE IF NOT EXISTS Accounts (
  AccountID MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  Disabled BOOLEAN NOT NULL DEFAULT 0,
  IsAdmin BOOLEAN NOT NULL DEFAULT 0,
  FailCount TINYINT UNSIGNED NOT NULL DEFAULT 0,
  Employer VARCHAR(64) NOT NULL DEFAULT '',
  PassHash VARCHAR(64) NOT NULL DEFAULT '',
  UserName varchar(50) NOT NULL UNIQUE,
  RealName VARCHAR(50) NOT NULL DEFAULT '',
  LastIP TINYTEXT,
  LastTime DATETIME,
  Created DATETIME,
  PRIMARY KEY (AccountID)
);

CREATE TABLE IF NOT EXISTS Sites (
  SiteID MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  Enabled BOOLEAN NOT NULL DEFAULT 0,
  SiteName VARCHAR(64) NOT NULL DEFAULT '',
  PRIMARY KEY (SiteID)
);

CREATE TABLE IF NOT EXISTS Timesheets (
  SheetID MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  AccountID MEDIUMINT UNSIGNED,
  SiteID MEDIUMINT UNSIGNED,
  Hours FLOAT NOT NULL DEFAULT 0.0,
  OtherSite TINYTEXT NOT NULL,
  WorkDate DATETIME,
  Modified DATETIME,
  Created DATETIME,
  PRIMARY KEY (SheetID),
  FOREIGN KEY (AccountID) REFERENCES Accounts(AccountID),
  FOREIGN KEY (SiteID) REFERENCES Sites(SiteID)
);

DROP TRIGGER IF EXISTS Accounts_OnInsert;
CREATE TRIGGER Accounts_OnInsert BEFORE INSERT ON `Accounts`
FOR EACH ROW SET NEW.Created = IFNULL(NEW.Created, NOW());

DROP TRIGGER IF EXISTS Timesheets_OnInsert;
CREATE TRIGGER Timesheets_OnInsert BEFORE INSERT ON `Timesheets`
FOR EACH ROW SET NEW.Created = IFNULL(NEW.Created, NOW());

INSERT INTO Sites (Enabled, SiteName)
VALUES (1, 'Other'); 

INSERT INTO Accounts (IsAdmin, UserName, RealName, PassHash)
VALUES (1, 'admin', 'administrator', 'fad86445aaa481bb69b8676a0ff39d7b65839339be673484b94d21fe9df3e953'); 