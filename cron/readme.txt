Crontab

# timecard every half hour cron jobs
1,31 7-20 * * * /usr/bin/wget -q --delete-after --no-check-certificate https://[DOMAIN_NAME]/cron/sync_protime_worklocation.php?cron_key=[CRON_KEY]

2,32 7-20 * * * /usr/bin/wget -q --delete-after --no-check-certificate https://[DOMAIN_NAME]/cron/sync_protime_curric.php?cron_key=[CRON_KEY]

3,33 7-20 * * * /usr/bin/wget -q --delete-after --no-check-certificate https://[DOMAIN_NAME]/cron/sync_protime_absence.php?cron_key=[CRON_KEY]

4,34 7-20 * * * /usr/bin/wget -q --delete-after --no-check-certificate https://[DOMAIN_NAME]/cron/sync_protime_p_limit.php?cron_key=[CRON_KEY]

5,35 7-20 * * * /usr/bin/wget -q --delete-after --no-check-certificate https://[DOMAIN_NAME]/cron/sync_protime_p_absence.php?cron_key=[CRON_KEY]

6,36 7-20 * * * /usr/bin/wget -q --delete-after --no-check-certificate https://[DOMAIN_NAME]/cron/sync_protime_bookings.php?cron_key=[CRON_KEY]

# run via cli, else timeout
7,37 7-20 * * * /usr/bin/php-cgi [/path/to/directory]/public_html/cron/sync_protime_pr_month.php cron_key=[CRON_KEY]

*/2 7-20 * * * /usr/bin/wget -q --delete-after --no-check-certificate https://[DOMAIN_NAME]/cron/sync_spec_protime_bookings_currentday.php?cron_key=[CRON_KEY]

# run via cli, else timeout
46 3 * * * /usr/bin/php-cgi [/path/to/directory]/public_html/cron/daily_maintenance.php cron_key=[CRON_KEY]



# timecard every tuesday cron jobs
0 8 * * 2 /usr/bin/wget -q --delete-after --no-check-certificate https://[DOMAIN_NAME]/mail/projectleader_project_weekhours.php?cron_key=[CRON_KEY]



# timecard only in the morning cron jobs
25 7 * * * /usr/bin/wget -q --delete-after --no-check-certificate https://[DOMAIN_NAME]/cron/sync_protime_cyc_dp.php?cron_key=[CRON_KEY]

26 7 * * * /usr/bin/wget -q --delete-after --no-check-certificate https://[DOMAIN_NAME]/cron/sync_protime_lnk_curric_profile.php?cron_key=[CRON_KEY]

27 7 * * * /usr/bin/wget -q --delete-after --no-check-certificate https://[DOMAIN_NAME]/cron/sync_protime_cycliq.php?cron_key=[CRON_KEY]

28 7 * * * /usr/bin/wget -q --delete-after --no-check-certificate https://[DOMAIN_NAME]/cron/sync_protime_dp_condition.php?cron_key=[CRON_KEY]

29 7 * * * /usr/bin/wget -q --delete-after --no-check-certificate https://[DOMAIN_NAME]/cron/sync_protime_dayprog.php?cron_key=[CRON_KEY]

30 7 * * * /usr/bin/wget -q --delete-after --no-check-certificate https://[DOMAIN_NAME]/cron/sync_protime_depart.php?cron_key=[CRON_KEY]
