# ==========================================================
# Resource Group
# ==========================================================
resource "azurerm_resource_group" "rg" {
  name     = "${var.project_name}-rg"
  location = var.location
}

# ==========================================================
# Storage Account - Application (gestionemployessa01)
# ==========================================================
resource "azurerm_storage_account" "sa" {
  name                     = "gestionemployessa01"
  resource_group_name      = azurerm_resource_group.rg.name
  location                 = azurerm_resource_group.rg.location
  account_tier             = "Standard"
  account_replication_type = "LRS"
  account_kind             = "StorageV2"
  min_tls_version          = "TLS1_2"

  tags = {
    project = var.project_name
    env     = "dev"
  }
}

# ==========================================================
# Blob Containers - data / documents
# ==========================================================
resource "azurerm_storage_container" "data" {
  name                  = "data"
  storage_account_name  = azurerm_storage_account.sa.name
  container_access_type = "private"
}

resource "azurerm_storage_container" "documents" {
  name                  = "documents"
  storage_account_name  = azurerm_storage_account.sa.name
  container_access_type = "private"
}

# ==========================================================
# Storage Account - Azure Function
# ==========================================================
resource "azurerm_storage_account" "func_sa" {
  name                     = "${replace(var.project_name, "-", "")}funcsa"
  resource_group_name      = azurerm_resource_group.rg.name
  location                 = azurerm_resource_group.rg.location
  account_tier             = "Standard"
  account_replication_type = "LRS"
  account_kind             = "StorageV2"
  min_tls_version          = "TLS1_2"

  tags = {
    project = var.project_name
    env     = "dev"
  }
}

# ==========================================================
# App Service Plan (Backend)
# ==========================================================
resource "azurerm_service_plan" "plan" {
  name                = "${var.project_name}-asp"
  resource_group_name = azurerm_resource_group.rg.name
  location            = azurerm_resource_group.rg.location
  os_type             = "Linux"
  sku_name            = "F1"
}

# ==========================================================
# Function App Plan (Consumption - Windows)
# ==========================================================
resource "azurerm_service_plan" "func_plan" {
  name                = "${var.project_name}-func-plan"
  resource_group_name = azurerm_resource_group.rg.name
  location            = azurerm_resource_group.rg.location

  os_type  = "Windows"
  sku_name = "Y1"
}

# ==========================================================
# Application Insights
# ==========================================================
resource "azurerm_application_insights" "appinsights" {
  name                = "${var.project_name}-ai"
  location            = azurerm_resource_group.rg.location
  resource_group_name = azurerm_resource_group.rg.name
  application_type    = "web"

  # Ne pas toucher au workspace Log Analytics déjà lié
  lifecycle {
    ignore_changes = [
      workspace_id
    ]
  }

  tags = {
    project = var.project_name
    env     = "dev"
  }
}

# ==========================================================
# App Service (Backend PHP / Laravel)
# ==========================================================
resource "azurerm_linux_web_app" "app" {
  name                = var.web_app_name
  resource_group_name = azurerm_resource_group.rg.name
  location            = azurerm_resource_group.rg.location
  service_plan_id     = azurerm_service_plan.plan.id

  site_config {
    always_on = false

    application_stack {
      php_version = "8.2"
    }
  }

  app_settings = {
    # Laravel core
    "APP_ENV"   = "production"
    "APP_DEBUG" = "false"
    "APP_KEY"   = "base64:iKhkkYDTar9JER9Wp4lQ0k+5sEsypcPZO61Pno+WSfM="

    # URL de la Web App
    "APP_URL"   = "https://${var.web_app_name}.azurewebsites.net"

    # Azure Blob Storage géré par Terraform
    "AZURE_STORAGE_CONNECTION_STRING" = azurerm_storage_account.sa.primary_connection_string
    "AZURE_STORAGE_CONTAINER"         = azurerm_storage_container.documents.name
    "AZURE_STORAGE_CONTAINER_DATA"    = azurerm_storage_container.data.name
    "FILESYSTEM_DISK"                 = "azure"

    # Logs / Insights
    "WEBSITES_ENABLE_APP_SERVICE_STORAGE"   = "false"
    "APPLICATIONINSIGHTS_CONNECTION_STRING" = azurerm_application_insights.appinsights.connection_string
  }

  https_only = true

  tags = {
    project = var.project_name
    env     = "dev"
  }
}

# ==========================================================
# Azure Function App (Automation / Reminders)
# ==========================================================
resource "azurerm_windows_function_app" "function" {
  name                = "${var.project_name}-function"
  resource_group_name = azurerm_resource_group.rg.name
  location            = azurerm_resource_group.rg.location

  service_plan_id            = azurerm_service_plan.func_plan.id
  storage_account_name       = azurerm_storage_account.func_sa.name
  storage_account_access_key = azurerm_storage_account.func_sa.primary_access_key

  site_config {
    application_stack {
      node_version = "~18"
    }
  }

  app_settings = {
    "FUNCTIONS_WORKER_RUNTIME" = "node"
    "WEBSITE_RUN_FROM_PACKAGE" = "1"
    "BACKEND_API_URL"          = "https://${var.web_app_name}.azurewebsites.net"
  }

  tags = {
    project = var.project_name
    env     = "dev"
  }
}
