-- Este script reflete a estrutura correta das tabelas no schema DBO.

-- Tabela de Setores
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SGM_Setores]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].SGM_Setores (
    Setor_TAG NVARCHAR(50) PRIMARY KEY NOT NULL,
    Nome_Setor NVARCHAR(100) NULL
);
END
GO

-- Tabela de Ativos
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SGM_Ativos]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].SGM_Ativos (
	ID INT IDENTITY(1,1) NOT NULL, -- Adicionando uma coluna ID que faltava no código anterior
	Ativo_TAG NVARCHAR(50) PRIMARY KEY NOT NULL,
	Setor_TAG NVARCHAR(50) NULL,
	Descricao TEXT NULL,
	Modelo VARCHAR(100) NULL,
	Numero_Serie VARCHAR(100) NULL,
	Tipo VARCHAR(20) NULL,
	Instalacao DATE NULL
);
END
GO

-- Tabela de Usuários
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SGM_Usuarios]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].SGM_Usuarios (
    codigo INT PRIMARY KEY NOT NULL,
    nome NVARCHAR(255) NOT NULL,
    ativo BIT DEFAULT 1,
    cliente BIT DEFAULT 0,
    tecnico BIT DEFAULT 0,
    planejador BIT DEFAULT 0,
    administrador BIT DEFAULT 0
);
END
GO

-- Tabela de Ordens de Serviço (OS)
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SGM_OS]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].SGM_OS (
	OS_ID INT IDENTITY(1,1) PRIMARY KEY NOT NULL,
	Ativo_TAG NVARCHAR(50) NULL,
	Solicitante INT NULL,
	Data_Solicitacao DATETIME NULL,
	Status VARCHAR(20) NULL,
	Data_Inicio_Atendimento DATETIME NULL,
	Data_Fim_Atendimento DATETIME NULL,
	Descricao_Servico TEXT NULL
);
END
GO

-- Tabela de Status (TAGs de Status para OS)
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SGM_TAGStatus]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].SGM_TAGStatus (
    TAGStatusID INT IDENTITY(1,1) PRIMARY KEY,
    TAGStatus NVARCHAR(50) NOT NULL
);
INSERT INTO [dbo].SGM_TAGStatus (TAGStatus) VALUES
('Aguardando Análise'), ('Aguardando Técnico'), ('Aguardando Terceiros'),
('Aguardando Peça'), ('Aguardando Liberação'), ('Aguardando Aprovação'),
('Em Atendimento'), ('Consolidando'), ('Em Testes'), ('Concluída');
END
GO

-- Tabela de Histórico de Status da OS
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SGM_OS_Status]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].SGM_OS_Status (
    codigo_os_status INT IDENTITY(1,1) PRIMARY KEY,
    os_tag NVARCHAR(50) NULL,
    tag_status_id INT NULL,
    data_inicio DATETIME NULL,
    data_fim DATETIME NULL,
    abriu_codigo INT NULL,
    fechou_codigo INT NULL,
    descricao NVARCHAR(MAX) NULL
);
END
GO

-- Tabela de Peças da OS
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SGM_OS_Pecas]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].SGM_OS_Pecas (
	ID INT IDENTITY(1,1) PRIMARY KEY NOT NULL,
	OS_ID INT NULL,
	Codigo_Peca VARCHAR(50) NULL,
	Descricao_Peca VARCHAR(200) NULL,
	Quantidade INT NULL,
	Custo_Unitario DECIMAL(10, 2) NULL
);
END
GO

-- Tabela de Atendimentos da OS
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SGM_OS_Atendimentos]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].SGM_OS_Atendimentos (
	ID INT IDENTITY(1,1) PRIMARY KEY NOT NULL,
	OS_ID INT NULL,
	Tecnico INT NULL,
	Data_Hora_Inicio DATETIME NULL,
	Data_Hora_Fim DATETIME NULL,
	Descricao_Atendimento TEXT NULL
);
END
GO

-- Tabela de Tarefas
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SGM_Tarefas]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].SGM_Tarefas (
    tarefa_codigo INT IDENTITY(1,1) PRIMARY KEY,
    tarefa_tag NVARCHAR(100) NOT NULL,
    ativo_tag NVARCHAR(50),
    tarefa_descricao NVARCHAR(MAX),
    ultima_execucao DATE
);
END
GO