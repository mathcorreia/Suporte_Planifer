CREATE TABLE OS_Pecas (
    cod_os_pecas INT IDENTITY(1,1) PRIMARY KEY,
    OS_TAG VARCHAR(50),
    codigo VARCHAR(50),
    quantidade INT,
    descricao NVARCHAR(MAX),
    custo_unitario DECIMAL(10,2),
    custo_total DECIMAL(10,2)
);

CREATE TABLE TAGStatus (
    TAGStatusID INT IDENTITY(1,1) PRIMARY KEY,
    TAGStatus NVARCHAR(50) NOT NULL
);

INSERT INTO TAGStatus (TAGStatus) VALUES 
('Aguardando Análise'),
('Aguardando Técnico'),
('Aguardando Terceiros'),
('Aguardando Peça'),
('Aguardando Liberação'),
('Aguardando Aprovação'),
('Em Andamento'),
('Em Fechamento'),
('Em Testes'),
('Concluída');

