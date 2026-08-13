import os
import json # Necessário para converter os dados do banco para texto (string)
from fastapi import FastAPI, HTTPException
import mysql.connector
import redis # Importando o cliente do Redis
from pydantic import BaseModel
# Importa a função do motor que acabamos de criar
from app.motor import executar_teste_carga

app = FastAPI(title="API Loja Genérica")

class ConfigTeste(BaseModel):
    url: str
    total: int
    concorrencia: int

@app.post("/api/teste-carga")
def disparar_teste(config: ConfigTeste):
    # Executa a carga pesada
    resultado = executar_teste_carga(config.url, config.total, config.concorrencia)
    return resultado

def get_db_connection():
    """Estabelece conexão com o banco de dados MySQL via Variáveis de Ambiente."""
    try:
        host = os.environ.get("DB_HOST", "localhost")
        port = os.environ.get("DB_PORT", "3306")
        dbname = os.environ.get("DB_NAME", "loja_vendas")
        user = os.environ.get("DB_USER", "root")
        password = os.environ.get("DB_PASSWORD", "sua_senha_aqui")

        conn = mysql.connector.connect(
            host=host,
            port=port,
            database=dbname,
            user=user,
            password=password
        )
        return conn
    except mysql.connector.Error as e:
        print(f"Erro de conexão MySQL: {e}")
        return None

def get_redis_connection():
    """Estabelece conexão com o Redis via Variáveis de Ambiente."""
    try:
        host = os.environ.get("REDIS_HOST", "localhost")
        port = int(os.environ.get("REDIS_PORT", 6379))
        
        # decode_responses=True já retorna os dados como string em vez de bytes
        cache = redis.Redis(host=host, port=port, decode_responses=True)
        return cache
    except redis.RedisError as e:
        print(f"Erro de conexão Redis: {e}")
        return None

# Definindo a rota que o index.php vai consumir
@app.get("/api/produtos")
def fetch_produtos_com_categorias():
    cache = get_redis_connection()
    cache_key = "lista_produtos_categorias"

    # PASSO 1: Tenta buscar do cache primeiro
    if cache:
        try:
            cached_data = cache.get(cache_key)
            if cached_data:
                print("Cache Hit! Retornando dados do Redis.")
                # O Redis salva como string, precisamos converter de volta para JSON/dicionário
                return json.loads(cached_data)
        except redis.RedisError as e:
            print(f"Falha ao ler do Redis: {e}")
            # Se o Redis falhar, não paramos a API. Deixamos seguir para o banco.

    # PASSO 2: Cache Miss! Busca no MySQL
    print("Cache Miss! Buscando no MySQL...")
    conn = get_db_connection()
    if not conn:
        raise HTTPException(status_code=500, detail="Erro ao conectar no banco de dados.")

    try:
        cur = conn.cursor(dictionary=True)
        
        query = """
            SELECT p.id, p.nome AS produto, p.preco, p.estoque, c.nome AS categoria
            FROM produtos p
            JOIN categorias c ON p.categoria_id = c.id
            ORDER BY p.id;
        """
        cur.execute(query)
        resultados = cur.fetchall()
        
        # PASSO 3: Salva o resultado no cache para as próximas requisições
        if cache:
            try:
                # O Redis precisa que dicionários/listas sejam convertidos em string
                dados_para_cache = json.dumps(resultados, default=str)
                
                # setex salva a chave com um TTL (Time To Live) de 60 segundos
                cache.setex(cache_key, 60, dados_para_cache)
                print("Dados salvos no Redis com sucesso!")
            except redis.RedisError as e:
                print(f"Falha ao salvar no Redis: {e}")
        
        return resultados
        
    except mysql.connector.Error as e:
        raise HTTPException(status_code=500, detail=str(e))
    finally:
        if 'cur' in locals():
            cur.close()
        if conn and conn.is_connected():
            conn.close()
