@echo off
title Utility Store Management Project Generator

echo Creating project structure...

mkdir "assets"
mkdir "assets\css"
mkdir "assets\js"
mkdir "assets\images"
mkdir "assets\uploads"
mkdir "assets\uploads\products"
mkdir "assets\uploads\users"
mkdir "assets\icons"

mkdir "config"
mkdir "includes"
mkdir "admin"
mkdir "customer"
mkdir "staff"
mkdir "api"
mkdir "models"
mkdir "database"
mkdir "logs"

type nul > index.php
type nul > login.php
type nul > register.php
type nul > logout.php
type nul > dashboard.php
type nul > README.md

type nul > config\database.php
type nul > config\config.php
type nul > config\session.php
type nul > config\constants.php

type nul > includes\header.php
type nul > includes\footer.php
type nul > includes\navbar.php
type nul > includes\sidebar.php
type nul > includes\functions.php
type nul > includes\auth.php

type nul > logs\activity.log

echo.
echo ============================================
echo Project Structure Created Successfully!
echo ============================================
pause