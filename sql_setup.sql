CREATE TABLE SGM_TAGStatus (
    TAGStatusID INT IDENTITY(1,1) PRIMARY KEY,
    TAGStatus NVARCHAR(50) NOT NULL
);
GO

-- Populando a tabela de Status
INSERT INTO SGM_TAGStatus (TAGStatus) VALUES
('Aguardando Análise'),
('Aguardando Técnico'),
('Aguardando Terceiros'),
('Aguardando Peça'),
('Aguardando Liberação'),
('Aguardando Aprovação'),
('Em Atendimento'),
('Consolidando'),
('Em Testes'),
('Concluída');
GO

-- Tabela de Usuários (simplificada, sem senha)
CREATE TABLE SGM_Usuarios (
    codigo INT PRIMARY KEY,
    nome NVARCHAR(255) NOT NULL,
    ativo BIT DEFAULT 1,
    cliente BIT DEFAULT 0,
    tecnico BIT DEFAULT 0,
    planejador BIT DEFAULT 0,
    administrador BIT DEFAULT 0
);
GO

-- Tabela de Ativos
CREATE TABLE SGM_Ativos (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ativo_tag NVARCHAR(50) UNIQUE NOT NULL,
    descricao NVARCHAR(MAX),
    setor_tag NVARCHAR(50),
    modelo NVARCHAR(100),
    numero_serie NVARCHAR(100),
    tipo NVARCHAR(50) -- TCNC, FCNC, MANUAL, INFO, PREDIAL, MAQUINA, OUTRO
);
GO

-- Tabela de Tarefas de Manutenção Preventiva
CREATE TABLE SGM_Tarefas (
    tarefa_codigo INT IDENTITY(1,1) PRIMARY KEY,
    tarefa_tag NVARCHAR(100) NOT NULL,
    ativo_tag NVARCHAR(50),
    tarefa_descricao NVARCHAR(MAX)
);
GO

-- Tabela principal de Ordens de Serviço (OS)
CREATE TABLE SGM_OS (
    os_tag NVARCHAR(50) PRIMARY KEY,
    ativo_tag NVARCHAR(50),
    maquina_parada BIT,
    os_tipo NVARCHAR(50),
    data_criacao DATETIME,
    data_conclusao DATETIME,
    solicitante_codigo INT,
    descricao_problema NVARCHAR(MAX)
);
GO

-- Tabela para o histórico de Status de cada OS
CREATE TABLE SGM_OS_Status (
    codigo_os_status INT IDENTITY(1,1) PRIMARY KEY,
    os_tag NVARCHAR(50),
    tag_status_id INT,
    data_inicio DATETIME,
    data_fim DATETIME,
    abriu_codigo INT,
    descricao NVARCHAR(MAX)
);
GO

-- Tabela para as Peças utilizadas em cada OS (fornecida por você)
CREATE TABLE SGM_OS_Pecas (
    cod_os_pecas INT IDENTITY(1,1) PRIMARY KEY,
    os_tag VARCHAR(50),
    codigo VARCHAR(50),
    quantidade INT,
    descricao NVARCHAR(MAX),
    custo_unitario DECIMAL(10,2),
    custo_total DECIMAL(10,2)
);
GO
