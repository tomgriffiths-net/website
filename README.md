# website
This is a package for PHP-CLI that implements a website into the program.

- **newSite(string|false $apacheRoot=false, string|false $directory=false, string|false $filesurl=false, string|false $filesdir=false, string|false $logsdir=false, string|false $tempdir=false, bool $communicator=false, string $name="MySite", string $password="1234"):int|false**: Creates a new website with the required files in place and set up, returns the site id on success or false on failure.
- **startSite(int $siteId):bool**: Tells communicator to start a site, returns true on success or false on failure.
- **stopSite(int $siteId):bool**: Tells communicator to stop a site, truens true on success or false on failure.
- **updateSite(int $siteId):bool**: Updates a websites files to the built in site files, returns true on success or false on failure.
- **removeSite(int $siteId):bool**: Stops and deletes a site from the settings, returns true on success or false on failure.
- **sendCommand(string $action, array $sites=[], array $servers=[]):array|false**: Sends a command to the communicator website hoster, returns an array with a success (bool) element and an error (string) element or false on failure to connect.
- **sendCommandBool(string $command, array $sites=[], array $servers=[]):bool**: Sends a command to the communicator website hoster, returns true on success or false on failure.
- **listSites():array|false**: Returns detailed information about the sites (not state information) on success or false on failure.
- **numberOfSites():int|false**: Returns the number of sites or false on failure.