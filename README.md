# Stat Tracker

Stat Tracker is a web app for [Ingress](http://ingress.com) agents to track and predict their progression to levels and badges, and compare themselves to other agents in their faction.

Stat Tracker relies on agents to submit their own data. It does not pull any data from the Ingress app or Niantic servers, and thus is not believed to violate the Ingress TOS.

## Setup

To run your own instance of Stat Tracker, you will need:

 * A LAMP (**L** inux, **A** pache, **M** ySQL, **P** HP) server
   * [composer](http://getcomposer.org), [lessc](http://lesscss.org/#using-less), [ocrad](http://www.gnu.org/software/ocrad/) and `make` must also be installed.
 * A [Google Developer](http://console.developers.google.com) account (for OAuth)
 * An SMTP server (Your existing email service should provide one)
 
1. In the web directory of your server, run `git clone https://github.com/BlueHerons/StatTracker.git` to pull down the latest code
2. Edit `config.php` and provide the required credentials
  * `GOOGLE_*` options are provided from the Google Developer account. You will need to create a new project within your account in order to obtain a client ID and client secret.
    * During the project setup, you will need to specify a callback URL for OAuth. This URL should be `http[s]://server/path/to/files/authenticate?action=callback`
3. Run `composer update` to download all the dependencies.
4. Execute each SQL script in `database/tables`, and then in `database/procedures`
  * You will need to create a MySQL user named `admin` to satisfy the definer in the procedure definitions.
5. In `Makefile`, change `CONFIG_BASE_URL` to the same `http[s]://server/path/to/files` you specified in the Google OAuth setup.
6. Run `make build` to automatically compile `style.less` and inject `CONFIG_BASE_URL` into [config.php](config.php#L16) and [StatTracker.js](scripts/StatTracker.js#L2)

## Administration

When a user tries to access Stat Tracker for the first time, they will be emailed an activation code -- 6 hexadecimal digits. The email instructs the user to send their activation code over Faction COMM in the scanner app to an agent (likely you). Once you receive an in-game message with an activation code:

1. Open up the `Agents` table
2. Find the row with the given activation code
3. Update the `agent` column to be the agents name that sent you the code.

**WARNING:** Once you do this, the user will have access to your instance of Stat Tracker. You *should* go to other lengths to verify the agent's identity. If you need to revoke access for any reason, set the value of the `agent` column to an empty string, or delete the entire row.
