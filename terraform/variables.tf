variable "subscription_id" {
  description = "Azure Subscription ID"
  type        = string
}

variable "project_name" {
  description = "Nom du projet (préfixe des ressources)"
  type        = string
  default     = "gestion-employes"
}

variable "location" {
  description = "Région Azure"
  type        = string
  default     = "francecentral"
}

# Nom de la Web App (utilisé sans self-reference)
variable "web_app_name" {
  description = "Nom de la Web App Azure (sans l'URL complète)"
  type        = string
  default     = "gestion-employes-app"
}
