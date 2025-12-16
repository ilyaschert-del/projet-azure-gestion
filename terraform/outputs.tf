output "resource_group_name" {
  value = azurerm_resource_group.rg.name
}

output "storage_account_name" {
  value = azurerm_storage_account.sa.name
}

output "storage_connection_string" {
  value     = azurerm_storage_account.sa.primary_connection_string
  sensitive = true
}

output "app_service_url" {
  value = azurerm_linux_web_app.app.default_hostname
}
