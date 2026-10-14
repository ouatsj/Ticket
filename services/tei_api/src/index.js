'use strict';

const express = require('express');
const cors = require('cors');
const config = require('./config');
const v1 = require('./routes/v1');

const app = express();
app.set('trust proxy', 1);
app.use(express.json({ limit: '256kb' }));
app.use(
  cors({
    origin(origin, cb) {
      if (!origin || config.corsOrigins.includes('*')) return cb(null, true);
      if (config.corsOrigins.some((o) => origin === o || (o.endsWith('*') && origin.startsWith(o.slice(0, -1))))) {
        return cb(null, true);
      }
      return cb(null, false);
    },
  })
);

app.get('/', (_req, res) => {
  res.json({
    name: 'Transport Executive Intelligence API',
    version: '1.0.0',
    docs: '/v1/health',
  });
});

app.use('/v1', v1);

app.use((err, _req, res, _next) => {
  // eslint-disable-next-line no-console
  console.error(err);
  res.status(500).json({ error: 'erreur serveur' });
});

app.listen(config.port, () => {
  // eslint-disable-next-line no-console
  console.log(`tei-api écoute sur :${config.port} (TZ=${config.tz})`);
});
