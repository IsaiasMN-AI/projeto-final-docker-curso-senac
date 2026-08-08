import os
from fastapi import FastAPI, HTTPException
import mysql.connector

# ESTA É A VARIÁVEL QUE O UVICORN ESTÁ PROCURANDO:
app = FastAPI(title="API Loja Genérica")

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
        print(f"Erro de conexão: {e}")
        return None

# Definindo a rota que o index.php vai consumir
@app.get("/api/produtos")
def fetch_produtos_com_categorias():
    conn = get_db_connection()
    if not conn:
        raise HTTPException(status_code=500, detail="Erro ao conectar no banco de dados.")

    try:
        # dictionary=True retorna os dados como chave-valor, e o FastAPI já converte pra JSON automático
        cur = conn.cursor(dictionary=True)
        
        query = """
            SELECT p.id, p.nome AS produto, p.preco, p.estoque, c.nome AS categoria
            FROM produtos p
            JOIN categorias c ON p.categoria_id = c.id
            ORDER BY p.id;
        """
        cur.execute(query)
        resultados = cur.fetchall()
        
        return resultados
        
    except mysql.connector.Error as e:
        raise HTTPException(status_code=500, detail=str(e))
    finally:
        if 'cur' in locals():
            cur.close()
        if conn.is_connected():
            conn.close()