CREATE DATABASE mwce_settings
ON
	PRIMARY (NAME=mwce_settingsData,
	FILENAME='c:\dbbkp\mwce_settings.mdf', -- <- change address if need
	SIZE=5,
	FILEGROWTH=10% )
LOG ON (
	NAME=mwce_settingsLog,
	FILENAME='c:\dbbkp\mwce_settings.ldf', -- <- change address if need
	SIZE=1,
	FILEGROWTH=1
	);