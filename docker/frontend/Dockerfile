# Stage 1: Build
FROM node:20-alpine AS builder

WORKDIR /app

COPY ./frontend/package*.json ./
RUN npm install

COPY ./frontend .

# Build args so Vite can embed env vars at build time
ARG VITE_API_URL
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME

ENV VITE_API_URL=$VITE_API_URL
ENV VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY
ENV VITE_REVERB_HOST=$VITE_REVERB_HOST
ENV VITE_REVERB_PORT=$VITE_REVERB_PORT
ENV VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME

RUN npm run build

# Stage 2: Serve with Nginx
FROM nginx:alpine

COPY --from=builder /app/dist /usr/share/nginx/html
COPY ./docker/frontend/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
