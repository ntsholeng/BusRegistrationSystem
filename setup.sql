-- Create the database
CREATE DATABASE IF NOT EXISTS BusRegistrationSystem;
USE BusRegistrationSystem;

-- Parent Table
CREATE TABLE Parent (
    ParentID INT PRIMARY KEY AUTO_INCREMENT,
    FullName VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL,
    PhoneNumber VARCHAR(20)
);

-- Learner Table
CREATE TABLE Learner (
    LearnerID INT PRIMARY KEY AUTO_INCREMENT,
    FullName VARCHAR(100) NOT NULL,
    Grade INT NOT NULL,
    Email VARCHAR(100),
    Registered BOOLEAN DEFAULT FALSE,
    ParentID INT,
    FOREIGN KEY (ParentID) REFERENCES Parent(ParentID)
);

-- Route Table
CREATE TABLE Route (
    RouteID INT PRIMARY KEY AUTO_INCREMENT,
    RouteName VARCHAR(100) NOT NULL,
    MorningDeparture TIME,
    AfternoonDeparture TIME
);

-- Bus Table
CREATE TABLE Bus (
    BusID INT PRIMARY KEY AUTO_INCREMENT,
    RouteID INT,
    Capacity INT NOT NULL,
    FOREIGN KEY (RouteID) REFERENCES Route(RouteID)
);

-- PickUpPoint Table
CREATE TABLE PickUpPoint (
    PointID INT PRIMARY KEY AUTO_INCREMENT,
    RouteID INT,
    Location VARCHAR(100),
    Sequence INT,
    Time TIME,
    FOREIGN KEY (RouteID) REFERENCES Route(RouteID)
);

-- DropOffPoint Table
CREATE TABLE DropOffPoint (
    PointID INT PRIMARY KEY AUTO_INCREMENT,
    RouteID INT,
    Location VARCHAR(100),
    Sequence INT,
    Time TIME,
    FOREIGN KEY (RouteID) REFERENCES Route(RouteID)
);

-- Application Table
CREATE TABLE Application (
    ApplicationID INT PRIMARY KEY AUTO_INCREMENT,
    LearnerID INT,
    RouteID INT,
    Status VARCHAR(20),
    DateApplied DATE,
    FOREIGN KEY (LearnerID) REFERENCES Learner(LearnerID),
    FOREIGN KEY (RouteID) REFERENCES Route(RouteID)
);

-- WaitingList Table
CREATE TABLE WaitingList (
    EntryID INT PRIMARY KEY AUTO_INCREMENT,
    LearnerID INT,
    RouteID INT,
    DateAdded DATE,
    FOREIGN KEY (LearnerID) REFERENCES Learner(LearnerID),
    FOREIGN KEY (RouteID) REFERENCES Route(RouteID)
);

-- EmailNotification Table
CREATE TABLE EmailNotification (
    NotificationID INT PRIMARY KEY AUTO_INCREMENT,
    ParentID INT,
    Subject VARCHAR(100),
    Message TEXT,
    SentOn DATETIME,
    FOREIGN KEY (ParentID) REFERENCES Parent(ParentID)
);

-- MISReport Table
CREATE TABLE MISReport (
    ReportID INT PRIMARY KEY AUTO_INCREMENT,
    ReportType VARCHAR(50),
    GeneratedOn DATE,
    Summary TEXT
);

-- BackupLog Table
CREATE TABLE BackupLog (
    BackupID INT PRIMARY KEY AUTO_INCREMENT,
    BackupDate DATE,
    BackupType VARCHAR(50),
    FilePath VARCHAR(255)
);
