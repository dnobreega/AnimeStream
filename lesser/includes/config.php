<?php
declare(strict_types=1);

const BASE_URL = '/AnimeStream';
const APP_NAME = 'YSA';
const APP_FULL_NAME = 'Your Secrect Archive';

// Ajuste apenas estes dados para o seu MySQL.
const DB_HOST = '127.0.0.1';
const DB_PORT = '3307';
const DB_NAME = 'anime_stream';
const DB_USER = 'root';
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';

const SESSION_NAME = 'ysa_session';
const REMEMBER_COOKIE = 'ysa_remember';
const REMEMBER_DAYS = 30;
const RESET_MINUTES = 30;
const PROFILE_MAX_SIZE = 2 * 1024 * 1024;
const PROFILE_UPLOAD_DIR = __DIR__ . '/../assets/uploads/perfil';
const PROFILE_UPLOAD_WEB = 'assets/uploads/perfil';
const BANNER_UPLOAD_DIR = __DIR__ . '/../assets/uploads/banners';
const BANNER_UPLOAD_WEB = 'assets/uploads/banners';
const BANNER_MAX_SIZE = 8 * 1024 * 1024;
const APP_DEBUG = true;

const APP_ENCRYPTION_KEY = '6Ehhierkjw7xXOk9YSKXo5ns61H4xWC5XGCUoUk+RS4=';

