SHELL			= /bin/bash

CONFIG_BASE_URL		= http:\/\/blueheronsresistance.com\/stats\/
.SILENT:

BUILD_DIR		= build
RSYNC_IGNORE_FILE	= .rsync_ignore

build: css, copy, config

clean:
	echo "  Cleaning build dir...";
	rm -rf $(BUILD_DIR);

config:
	echo "  Updating configuration...";
	sed -i -e 's/\(\s*\)\(this.baseUrl = \).*/\1\2"$(CONFIG_BASE_URL)";/' $(BUILD_DIR)/scripts/stattracker.js;

copy:
	echo "  Copying files...";
	rsync -r --exclude-from $(RSYNC_IGNORE_FILE) ./ $(BUILD_DIR);

css:
	echo "  Compiling LESS...";
	lessc style.less > style.css;

