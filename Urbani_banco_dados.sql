

CREATE TABLE Perfil_acesso (
  Id_acesso       INT           NOT NULL AUTO_INCREMENT,
  Nome            VARCHAR(100)  NOT NULL,
  Lista_permissao TEXT,
  PRIMARY KEY (Id_acesso)
);

CREATE TABLE Categoria (
  id_categoria INT           NOT NULL AUTO_INCREMENT,
  Descricao    VARCHAR(200),
  PRIMARY KEY (id_categoria)
);

CREATE TABLE Cidadao (
  id_cidadao      INT          NOT NULL AUTO_INCREMENT,
  CPF             CHAR(11)     NOT NULL UNIQUE,
  Nome            VARCHAR(100) NOT NULL,
  e_mail          VARCHAR(100) NOT NULL UNIQUE,
  Telefone        VARCHAR(20),
  Data_nascimento DATE,
  Cidade          VARCHAR(100) NOT NULL,        
  Bairro          VARCHAR(100) NOT NULL,       
  senha           VARCHAR(255) NOT NULL,        
  data_cadastro   DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_cidadao)
);

CREATE TABLE Moderador (
  id_moderador    INT          NOT NULL AUTO_INCREMENT,
  Nome            VARCHAR(100) NOT NULL,
  email_moderador VARCHAR(100),
  PRIMARY KEY (id_moderador)
);

CREATE TABLE Administrador (
  id_administrador INT          NOT NULL AUTO_INCREMENT,
  nome             VARCHAR(100) NOT NULL,
  Cargo            VARCHAR(100),
  id_perfil_acesso INT          NOT NULL,
  PRIMARY KEY (id_administrador),
  CONSTRAINT fk_adm_perfil
    FOREIGN KEY (id_perfil_acesso)
    REFERENCES Perfil_acesso (Id_acesso)
    ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE Recursos (
  Id_recurso  INT            NOT NULL AUTO_INCREMENT,
  Preco_total DECIMAL(10, 2) DEFAULT 0.00,
  PRIMARY KEY (Id_recurso)
);

CREATE TABLE Estoque (
  Id_estoque INT NOT NULL AUTO_INCREMENT,
  fk_recurso INT NOT NULL,
  Quantidade INT NOT NULL DEFAULT 0,
  PRIMARY KEY (Id_estoque),
  CONSTRAINT fk_estoque_recurso
    FOREIGN KEY (fk_recurso)
    REFERENCES Recursos (Id_recurso)
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Solicitacao (
  id_solicitacao INT          NOT NULL AUTO_INCREMENT,
  fk_cidadao     INT          NOT NULL,
  fk_categoria   INT          NOT NULL,
  descricao      TEXT,
  Status         VARCHAR(50)  DEFAULT 'Aberta',
  PRIMARY KEY (id_solicitacao),
  CONSTRAINT fk_sol_cidadao
    FOREIGN KEY (fk_cidadao)
    REFERENCES Cidadao (id_cidadao)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_sol_categoria
    FOREIGN KEY (fk_categoria)
    REFERENCES Categoria (id_categoria)
    ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE Midia (
  Id_midia       INT          NOT NULL AUTO_INCREMENT,
  id_solicitacao INT          NOT NULL,
  Arquivo        VARCHAR(255) NOT NULL,
  data_upload    DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (Id_midia),
  CONSTRAINT fk_midia_solicitacao
    FOREIGN KEY (id_solicitacao)
    REFERENCES Solicitacao (id_solicitacao)
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE HistoricoStatus (
  id_status       INT         NOT NULL AUTO_INCREMENT,
  id_solicitacao  INT         NOT NULL,
  status_anterior VARCHAR(50),
  status_novo     VARCHAR(50) NOT NULL,
  data_mudanca    DATETIME    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_status),
  CONSTRAINT fk_hist_solicitacao
    FOREIGN KEY (id_solicitacao)
    REFERENCES Solicitacao (id_solicitacao)
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Analisa (
  id_moderador   INT NOT NULL,
  id_solicitacao INT NOT NULL,
  PRIMARY KEY (id_moderador, id_solicitacao),
  CONSTRAINT fk_analisa_mod
    FOREIGN KEY (id_moderador)
    REFERENCES Moderador (id_moderador)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_analisa_sol
    FOREIGN KEY (id_solicitacao)
    REFERENCES Solicitacao (id_solicitacao)
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Aprova_Rejeita (
  id_administrador INT NOT NULL,
  id_solicitacao   INT NOT NULL,
  PRIMARY KEY (id_administrador, id_solicitacao),
  CONSTRAINT fk_apr_adm
    FOREIGN KEY (id_administrador)
    REFERENCES Administrador (id_administrador)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_apr_sol
    FOREIGN KEY (id_solicitacao)
    REFERENCES Solicitacao (id_solicitacao)
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Necessita (
  id_solicitacao INT NOT NULL,
  Id_recurso     INT NOT NULL,
  PRIMARY KEY (id_solicitacao, Id_recurso),
  CONSTRAINT fk_nec_sol
    FOREIGN KEY (id_solicitacao)
    REFERENCES Solicitacao (id_solicitacao)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_nec_rec
    FOREIGN KEY (Id_recurso)
    REFERENCES Recursos (Id_recurso)
    ON DELETE CASCADE ON UPDATE CASCADE
);
