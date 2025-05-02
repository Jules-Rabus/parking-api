FROM node:23-alpine AS deps
WORKDIR /app

COPY --chown=node:node package*.json ./
RUN npm ci --quiet && npm cache clean --force

# Development stage
FROM deps AS dev

USER node
COPY --chown=node:node . .
EXPOSE 3000
CMD ["npm", "run", "dev"]

# Test stage with Cypress
FROM cypress/included:14.3.2 AS cypress

WORKDIR /e2e

COPY package*.json ./
RUN npm ci --quiet && npm cache clean --force

COPY . .

ENV CYPRESS_CACHE_FOLDER=/root/.cache/Cypress

CMD [ "npx", "cypress", "run" ]

# Build stage for production assets
FROM deps AS builder
COPY --chown=node:node . .
RUN npm run build

# PRODUCTION STAGE
FROM node:23-alpine AS prod
WORKDIR /app

ENV NODE_ENV=production

COPY --from=deps /app/node_modules ./node_modules
COPY --from=builder --chown=node:node /app/.next ./.next
COPY --from=builder --chown=node:node /app/public ./public
COPY --from=builder /app/package.json ./package.json

USER node
EXPOSE 3000
CMD ["npm", "start"]