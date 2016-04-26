1.0.3

- Dropped PHP 5.4 from test matrix (SlmQueue didn't support it anyway). Added PHP7.0 to testing matrix.

1.0.2

- Jobs that are waiting on job that have been buried will now be buried themselves.

1.0.1

- Binding to process.job event is now at highest possible priority. Therefore when necessary event propagation is
 stopped at the earliest possible moment.

1.0.0

- initial release