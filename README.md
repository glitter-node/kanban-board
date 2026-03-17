# KanbanBoard

A modern realtime Kanban board application built with **Laravel 12**, **Livewire**, and **Tailwind CSS**.

KanbanBoard provides a collaborative task management environment with realtime synchronization, role-based access control, and a scalable SaaS-style UI architecture.

---

## Features

### Realtime Collaboration
- WebSocket-based realtime synchronization using **Laravel Reverb**
- Card and column updates reflected instantly across connected clients

### Board & Task Management
- Drag-and-drop card movement
- Column reordering
- Task priority and due dates
- Member assignment

### Access Control
- Role-based permissions
  - **Owner**
  - **Editor**
  - **Viewer**

### Search & Filtering
- Full-text search using **Laravel Scout**
- Filter by:
  - priority
  - assigned user
  - due date

### Comments & Activity
- Realtime card comments
- Activity feed for board actions

### Notifications
- Card assignment
- Comment activity
- Due date reminders

### UI / UX
- Token-based design system
- Component-driven Blade UI
- Motion system for interactions
- Dark mode support

### Performance
- Redis caching
- Optimized database queries
- Indexed database schema

---

## Tech Stack

### Backend
- PHP 8.2+
- Laravel 12.x
- Laravel Reverb (WebSocket)

### Frontend
- Blade
- Livewire
- Tailwind CSS
- Alpine.js

### Infrastructure
- SQLite (default)
- Redis (cache + queue)

### Search
- Laravel Scout (Database driver)

### Build
- Node.js
- Vite

---

## Requirements

- PHP 8.2+
- Composer 2.x
- Node.js 18+
- Redis 7+
- SQLite 3

---

## Installation

```bash
git clone https://github.com/glitter-node/kanban-board.git
cd kanban-board
```
Install dependencies:
```bash
composer install
npm install
```
Create environment file:
```bash
cp .env.example .env
php artisan key:generate
```
Run migrations:
```bash
php artisan migrate
```
Optional seed data:
```bash
php artisan db:seed
```

---

## Development

Start the development server:
```bash
composer dev
```
This command starts:
- Laravel development server
- Queue worker
- Log monitor
- Vite dev server
App URL:
```bash
http://localhost:8000
```

---

## Realtime WebSocket

Realtime updates require the Reverb server.

Run in a separate terminal:
```bash
php artisan reverb:start
```
WebSocket endpoint:
```bash
wss://reverb-ws.glitter.tw
```

---

## Manual Development Setup

If not using composer dev:

Terminal 1:
```bash
php artisan serve
```
Terminal 2:
```bash
php artisan reverb:start
```
Terminal 3:
```bash
npm run dev
```

---

## Usage

### Create a Board
1. Login
2. Go to My Boards
3. Click Create Board

### Add Columns
Typical workflow:
```
To Do
In Progress
Done
```

### Add Cards

Each card supports:
- title
- description
- priority
- due date
- assigned user

### Drag & Drop

- Move cards between columns
- Reorder columns
- Touch support for mobile

---

## Search & Filters

Search cards by:
- title
- description
Filter cards by:
- priority
- assigned user
- due date

---

## Export

Boards can be exported as:
- JSON
- Markdown

---

## Testing

Run all tests:
```bash
php artisan test
```
Run unit tests:
```bash
php artisan test --testsuite=Unit
```
Run feature tests:
```bash
php artisan test --testsuite=Feature
```
Code style check:
```bash
vendor/bin/pint --test
```
Auto fix style:
```bash
vendor/bin/pint
```

---

## API

All API routes require session authentication.

Rate limit:
```
60 requests per minute
```

### Boards
```
GET /boards
POST /boards
GET /boards/{id}
PUT /boards/{id}
DELETE /boards/{id}
```

### Cards
```
POST /api/boards/{board}/columns/{column}/cards
PUT /api/boards/{board}/cards/{card}
DELETE /api/boards/{board}/cards/{card}
POST /api/boards/{board}/cards/{card}/move
```

### Comments
```
GET /api/boards/{board}/cards/{card}/comments
POST /api/boards/{board}/cards/{card}/comments
```

---

## WebSocket Events

Channel:
```
private-board.{boardId}
```
Events:
```
CardCreated
CardUpdated
CardMoved
ColumnCreated
ColumnUpdated
CommentCreated
ActivityLogged
```

---

## Authorization Roles

| Role   | Permissions              |
| ------ | ------------------------ |
| Owner  | Full board control       |
| Editor | Card & column management |
| Viewer | Read-only                |


---

## License

MIT License
