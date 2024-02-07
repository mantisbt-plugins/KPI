# KPI
Key performance indicators ready to download

How does it work?
This plugin calculates the number of days between 2 statusses en evaluates that against a set number of days.
Default it will calculate between Confirm date and Resolved date.
Only issues with a date on Resolving are selected.
Next the last confirm date is retrieved.
If not found, it will retrieve the last assign date.
If not found, it will use the date of submittal.
Next these 2 dates are compared and if the period is less or equal than the set number of days (default 2), it will qualify the issue resolved within agreed period.
Output is available on screen and as download.
