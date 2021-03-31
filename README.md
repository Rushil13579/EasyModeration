# EasyModeration
An all-in-one Moderation plugin for Pocketmine-MP

- If you have any suggestions/ideas/questions/bugs go [here](https://github.com/Rushil13579/EasyModeration/issues) or contact me on discord at **Rushil#2326**

## Features

- Discord Webhook support for all commands
- Chat Interval
- Chat Censor
- Cooldowns for Reports

## Commands & Permissions
Commands | Aliases | Permissions | Usage
---------|---------|-------------|------
/ipban | none | easymoderation.ipban | /ipban <address|name> [reason...]
/tempipban | none | easymoderation.tempipban | /tempipban <address|name> <time> [reason...]
/ipunban | none | easymoderation.ipunban | /ipunban <address>
/permban | none | easymoderation.permban | /permban <name> [reason...]
/tempban | none | easymoderation.tempban | /tempban <name> <time> [reason...]
/unban | none | easymoderation.unban | /unban <name>
/kick | none | easymoderation.kick | /kick <name> [reason...]
/kickall | none | easymoderation.kickall | /kickall [reason...]
/ipmute | none | easymoderation.ipmute | /ipmute <address|name> <time> [reason...]
/ipunmute | none | easymoderation.ipunmute | /ipunmute <address|name>
/mute | none | easymoderation.mute | /mute <name> <time> [reason...]
/unmute | none | easymoderation.unmute | /unmute <name>
/mutelist | none | easymoderation.mutelist | /mutelist [players|ips]
/mutechat | none | easymoderation.mutechat | /mutechat [reason...]
/warn | none | easymoderation.warn | /warn <name> [reason...]
/report | none | easymoderation.report | /report <name> [reason...]
/alts | none | easymoderation.alts | /alts <name>
/spy | none | easymoderation.spy | /spy
/staffchat | /sc | easymoderation.staffchat | /staffchat
/vanish | /v | easymoderation.vanish | /vanish
/freeze | none | easymoderation.freeze | /freeze <name>
/unfreeze | none | easymoderation.unfreeze | /unfreeze <name>

## Explanation of a few commands
- For any time argument, y -> years, mo -> months, d -> days, h -> hours, m -> minutes, s ->seconds. Eg: /tempban Ign 1y6mo Reason
- Incase of mute & ipmute, use [inf, infinite, perm, permanent, forever] for a perm mute/ipmute
- /mutelist gives a list of all muted players on the server. /mutelist ips will give a list of all muted ips on the server
- /report allows a player to report a fellow player for misconduct on the server
- /alts allows you to get the list of alternate accounts of a player that have logged on the server
- /spy allows you to get a live feed of the commands ran by all players
- Incase you dont want to toggle staffchat everytime, a '#' prefix before any message will be sent to the staffchat

## Future Plans
- Invsee feature for staff
- Punishment tracker (see past permissions)
- Auto ban (specific number of warns leads to a ban/mute)
- Discord webhooks for commands run, joins, chat, leaves, kills

## Credits
- [Rushil13579](https://github.com/Rushil13579)
