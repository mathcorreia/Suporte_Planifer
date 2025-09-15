-- PASSO 1: Criar o Schema
-- Se este comando falhar, o utilizador 'root' pode não ter permissão.
-- O nome do schema é "SISTEMAS_SUPORTE" (no plural).
IF NOT EXISTS (SELECT * FROM sys.schemas WHERE name = 'SISTEMAS_SUPORTE')
BEGIN
    EXEC('CREATE SCHEMA SISTEMAS_SUPORTE');
END
GO

-- PASSO 2: Criar todas as tabelas dentro do Schema

-- Tabela de Status para as Ordens de Serviço
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[SISTEMAS_SUPORTE].[SGM_TAGStatus]') AND type in (N'U'))
BEGIN
CREATE TABLE [SISTEMAS_SUPORTE].SGM_TAGStatus (
    TAGStatusID INT IDENTITY(1,1) PRIMARY KEY,
    TAGStatus NVARCHAR(50) NOT NULL
);
INSERT INTO [SISTEMAS_SUPORTE].SGM_TAGStatus (TAGStatus) VALUES
('Aguardando Análise'), ('Aguardando Técnico'), ('Aguardando Terceiros'),
('Aguardando Peça'), ('Aguardando Liberação'), ('Aguardando Aprovação'),
('Em Atendimento'), ('Consolidando'), ('Em Testes'), ('Concluída');
END
GO

-- Tabela de Usuários
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[SISTEMAS_SUPORTE].[SGM_Usuarios]') AND type in (N'U'))
BEGIN
CREATE TABLE [SISTEMAS_SUPORTE].SGM_Usuarios (
    codigo INT PRIMARY KEY,
    nome NVARCHAR(255) NOT NULL,
    ativo BIT DEFAULT 1,
    cliente BIT DEFAULT 0,
    tecnico BIT DEFAULT 0,
    planejador BIT DEFAULT 0,
    administrador BIT DEFAULT 0
);
END
GO

-- Tabela de Ativos
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[SISTEMAS_SUPORTE].[SGM_Ativos]') AND type in (N'U'))
BEGIN
CREATE TABLE [SISTEMAS_SUPORTE].SGM_Ativos (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ativo_tag NVARCHAR(50) UNIQUE NOT NULL,
    descricao NVARCHAR(MAX),
    setor_tag NVARCHAR(50),
    modelo NVARCHAR(100),
    numero_serie NVARCHAR(100),
    tipo NVARCHAR(50),
    instalacao DATE
);
END
GO

-- Tabela de Tarefas
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[SISTEMAS_SUPOPRTE].[SGM_Tarefas]') AND type in (N'U'))
BEGIN
CREATE TABLE [SISTEMAS_SUPORTE].SGM_Tarefas (
    tarefa_codigo INT IDENTITY(1,1) PRIMARY KEY,
    tarefa_tag NVARCHAR(100) NOT NULL,
    ativo_tag NVARCHAR(50),
    tarefa_descricao NVARCHAR(MAX),
    ultima_execucao DATE
);
END
GO

-- Tabela de Ordens de Serviço (OS)
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[SISTEMAS_SUPORTE].[SGM_OS]') AND type in (N'U'))
BEGIN
CREATE TABLE [SISTEMAS_SUPORTE].SGM_OS (
    os_tag NVARCHAR(50) PRIMARY KEY,
    ativo_tag NVARCHAR(50),
    maquina_parada BIT,
    os_tipo NVARCHAR(50),
    data_criacao DATETIME,
    data_conclusao DATETIME,
    solicitante INT,
    descricao_problema NVARCHAR(MAX)
);
END
GO

-- Tabela de Histórico de Status da OS
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[SISTEMAS_SUPORTE].[SGM_OS_Status]') AND type in (N'U'))
BEGIN
CREATE TABLE [SISTEMAS_SUPORTE].SGM_OS_Status (
    codigo_os_status INT IDENTITY(1,1) PRIMARY KEY,
    os_tag NVARCHAR(50),
    tag_status_id INT,
    data_inicio DATETIME,
    data_fim DATETIME,
    abriu_codigo INT,
    fechou_codigo INT,
    descricao NVARCHAR(MAX)
);
END
GO

-- Tabela de Peças da OS
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[SISTEMAS_SUPORTE].[SGM_OS_Pecas]') AND type in (N'U'))
BEGIN
CREATE TABLE [SISTEMAS_SUPORTE].SGM_OS_Pecas (
    cod_os_pecas INT IDENTITY(1,1) PRIMARY KEY,
    os_tag VARCHAR(50),
    codigo VARCHAR(50),
    quantidade INT,
    descricao NVARCHAR(MAX),
    custo_unitario DECIMAL(10,2),
    custo_total DECIMAL(10,2)
);
END
GO