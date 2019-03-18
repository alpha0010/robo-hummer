#!/bin/bash

/etc/init.d/rsyslog start      \
	&& service cron start      \
	&& tail -f /var/log/syslog &

PID=$!                              \
	&& function forward_sigterm()
		{
			service cron stop
			kill -TERM $PID
		}                           \
	&& trap forward_sigterm SIGTERM \
	&& wait $PID
