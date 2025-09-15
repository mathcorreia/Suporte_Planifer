-- Garante que a tabela SGM_TAGStatus exista antes de inserir dados
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SGM_TAGStatus]') AND type in (N'U'))
BEGIN
CREATE TABLE SGM_TAGStatus (
    TAGStatusID INT IDENTITY(1,1) PRIMARY KEY,
    TAGStatus NVARCHAR(50) NOT NULL
);
-- Popula a tabela de Status apenas se ela foi recém-criada
INSERT INTO SGM_TAGStatus (TAGStatus) VALUES
('Aguardando Análise'), ('Aguardando Técnico'), ('Aguardando Terceiros'),
('Aguardando Peça'), ('Aguardando Liberação'), ('Aguardando Aprovação'),
('Em Atendimento'), ('Consolidando'), ('Em Testes'), ('Concluída');
END
GO

-- Garante que a tabela SGM_Usuarios exista (sem senha)
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SGM_Usuarios]') AND type in (N'U'))
BEGIN
CREATE TABLE SGM_Usuarios (
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

-- Remove a coluna 'senha' da tabela SGM_Usuarios se ela existir
IF EXISTS (SELECT * FROM sys.columns WHERE Name = N'senha' AND Object_ID = Object_ID(N'SGM_Usuarios'))
BEGIN
    ALTER TABLE SGM_Usuarios DROP COLUMN senha;
END
GO

-- Garante que a tabela SGM_Ativos exista
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SGM_Ativos]') AND type in (N'U'))
BEGIN
CREATE TABLE SGM_Ativos (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ativo_tag NVARCHAR(50) UNIQUE NOT NULL,
    descricao NVARCHAR(MAX),
    setor_tag NVARCHAR(50),
    modelo NVARCHAR(100),
    numero_serie NVARCHAR(100),
    tipo NVARCHAR(50)
);
END
GO

-- Adiciona a coluna 'instalacao' à tabela SGM_Ativos se ela não existir
IF NOT EXISTS (SELECT * FROM sys.columns WHERE Name = N'instalacao' AND Object_ID = Object_ID(N'SGM_Ativos'))
BEGIN
    ALTER TABLE SGM_Ativos ADD instalacao DATE;
END
GO

-- As tabelas restantes são criadas apenas se não existirem

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SGM_Tarefas]') AND type in (N'U'))
BEGIN
CREATE TABLE SGM_Tarefas (
    tarefa_codigo INT IDENTITY(1,1) PRIMARY KEY,
    tarefa_tag NVARCHAR(100) NOT NULL,
    ativo_tag NVARCHAR(50),
    tarefa_descricao NVARCHAR(MAX),
    ultima_execucao DATE
);
END
GO

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SGM_OS]') AND type in (N'U'))
BEGIN
CREATE TABLE SGM_OS (
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

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SGM_OS_Status]') AND type in (N'U'))
BEGIN
CREATE TABLE SGM_OS_Status (
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

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SGM_OS_Pecas]') AND type in (N'U'))
BEGIN
CREATE TABLE SGM_OS_Pecas (
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