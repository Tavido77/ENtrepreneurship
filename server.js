const http = require('http');
const fs = require('fs');
const path = require('path');

const PORT = process.env.PORT || 3000;

// In-memory demo data (can be moved to a separate file)
let services = [
  { id: 1, name: 'Cut & Style', duration: 45, price: 40, tags: ['cut', 'style'], desc: 'Classic cut and styling.' },
  { id: 2, name: 'Beard Trim', duration: 20, price: 15, tags: ['beard'], desc: 'Neat beard shaping and trim.' },
  { id: 3, name: 'Colour & Highlights', duration: 90, price: 120, tags: ['colour'], desc: 'Full colour and highlight service.' }
];

let bookings = [];

function sendJSON(res, status, obj){
  const body = JSON.stringify(obj);
  res.writeHead(status, {
    'Content-Type': 'application/json',
    'Content-Length': Buffer.byteLength(body),
  });
  res.end(body);
}

function serveStatic(req, res, filePath){
  fs.stat(filePath, (err, stats) => {
    if (err) return send404(req, res);
    if (stats.isDirectory()) filePath = path.join(filePath, 'index.html');
    const ext = path.extname(filePath).toLowerCase();
    const mime = {
      '.html': 'text/html; charset=utf-8',
      '.js': 'application/javascript; charset=utf-8',
      '.css': 'text/css; charset=utf-8',
      '.png': 'image/png',
      '.jpg': 'image/jpeg',
      '.svg': 'image/svg+xml',
      '.json': 'application/json'
    }[ext] || 'application/octet-stream';

    res.writeHead(200, { 'Content-Type': mime });
    const stream = fs.createReadStream(filePath);
    stream.pipe(res);
    stream.on('error', () => send404(req, res));
  });
}

function send404(req, res){
  res.writeHead(404, { 'Content-Type': 'text/plain' });
  res.end('Not found');
}

const server = http.createServer((req, res) => {
  const { method, url } = req;
  const parsedUrl = new URL(url, `http://${req.headers.host}`);
  const pathname = parsedUrl.pathname;

  // Simple API router
  if (pathname.startsWith('/api')){
    // Basic CORS for flexibility (if frontend served elsewhere)
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET,POST,OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
    if (method === 'OPTIONS') return sendJSON(res, 204, {});

    if (method === 'GET' && pathname === '/api/services'){
      return sendJSON(res, 200, { services });
    }

    if (method === 'GET' && pathname === '/api/appointments'){
      return sendJSON(res, 200, { appointments: bookings });
    }

    if (method === 'POST' && pathname === '/api/bookings'){
      let body = '';
      req.on('data', chunk => body += chunk);
      req.on('end', () => {
        try {
          const data = JSON.parse(body);
          // Basic validation
          if (!data.serviceId || !data.date) return sendJSON(res, 400, { error: 'serviceId and date required' });
          const service = services.find(s => s.id === Number(data.serviceId));
          if (!service) return sendJSON(res, 404, { error: 'service not found' });

          const newBooking = {
            id: bookings.length + 1,
            serviceId: service.id,
            serviceName: service.name,
            date: data.date,
            time: data.time || null,
            stylist: data.stylist || null,
            price: service.price,
            createdAt: new Date().toISOString()
          };
          bookings.push(newBooking);
          return sendJSON(res, 201, newBooking);
        } catch (e){
          return sendJSON(res, 400, { error: 'invalid JSON' });
        }
      });
      return;
    }

    return sendJSON(res, 404, { error: 'API route not found' });
  }

  // Static file serving: map url to workspace files
  let safePath = path.normalize(decodeURIComponent(parsedUrl.pathname)).replace(/^\/+/, '');
  if (!safePath) safePath = 'index.html';
  const filePath = path.join(__dirname, safePath);

  // Prevent path traversal
  if (!filePath.startsWith(__dirname)) return send404(req, res);
  serveStatic(req, res, filePath);
});

server.listen(PORT, () => {
  console.log(`Server listening on http://localhost:${PORT}`);
});
