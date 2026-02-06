# 📅 Room Booking System (GAPRE)

Sistema de agendamento de salas de reunião desenvolvido para otimizar a gestão de espaços comuns entre secretarias.

## 🚀 Funcionalidades

- **Controle de Acesso:** Diferentes níveis de permissão (Admin, TI, Secretário).
- **Gestão de Reservas:** Cadastro, visualização e cancelamento de agendamentos.
- **Filtros Inteligentes:** Busca por mês e ano para facilitar o histórico.
- **Segurança:** Proteção contra ataques CSRF e hashing de senhas com `password_hash`.
- **Interface Responsiva:** Desenvolvido com Bootstrap 5.

## 🛠️ Tecnologias Utilizadas

- **PHP 8.x** (Lógica de backend)
- **MySQL** (Banco de dados)
- **Bootstrap 5** (Interface UI)
- **PDO** (Camada de segurança para conexão com banco)

## 📂 Estrutura de Arquivos

| Arquivo | Descrição |
| :--- | :--- |
| `index.php` | Tela de login e autenticação. |
| `dashboard.php` | Painel principal com calendário e formulário de reserva. |
| `database.php` | Configuração da conexão com o banco de dados. |
| `booking_create.php` | Processamento de novas reservas. |
| `booking_cancel.php` | Lógica para cancelamento de agendamentos. |
| `user_register.php` | Cadastro de novos usuários (Restrito ao perfil TI). |
| `logout.php` | Encerramento seguro da sessão. |

## 🔧 Instalação e Configuração

1. **Clonar o repositório:**
   ```bash
   git clone [https://github.com/seu-usuario/seu-repositorio.git](https://github.com/seu-usuario/seu-repositorio.git)
