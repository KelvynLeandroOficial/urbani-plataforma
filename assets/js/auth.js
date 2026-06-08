document.addEventListener("DOMContentLoaded", () => {
    
    // Configura o formulário de Cadastro (Registro)
    const registroForm = document.querySelector('form[action="processa_registro.php"]');
    if (registroForm) {
        registroForm.addEventListener("submit", async (e) => {
            e.preventDefault(); // Impede o reload padrão da página

            const formData = new FormData(registroForm);
            const data = Object.fromEntries(formData.entries());

            // Validação de tamanho mínimo do CPF em conformidade com o tipo CHAR(11) do banco
            if (data.cpf.replace(/\D/g, "").length !== 11) {
                alert("O CPF deve conter exatamente 11 dígitos numéricos.");
                return;
            }

            try {
                const response = await fetch("backend/auth.php?action=register", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert("Sua conta foi criada com sucesso!");
                    window.location.href = "index.html"; // Redireciona para a tela de login (Frame 1)
                } else {
                    alert("Erro ao cadastrar: " + result.message);
                }
            } catch (error) {
                console.error("Falha na requisição:", error);
                alert("Erro ao conectar com o servidor.");
            }
        });
    }

    // Configura o formulário de Login (Autenticação)
    const loginForm = document.querySelector('form[action="dashboard.html"]');
    if (loginForm) {
        loginForm.addEventListener("submit", async (e) => {
            e.preventDefault();

            const formData = new FormData(loginForm);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch("backend/auth.php?action=login", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    // Salva as informações da sessão do Cidadão no navegador
                    sessionStorage.setItem("user_id", result.user.id_usuario);
                    sessionStorage.setItem("user_name", result.user.nome);
                    sessionStorage.setItem("user_type", result.user.tipo_usuario);
                    
                    // Avança o usuário de forma bem-sucedida para o Dashboard (Frame 4)
                    window.location.href = "dashboard.html"; 
                } else {
                    alert("Falha ao entrar: " + result.message);
                }
            } catch (error) {
                console.error("Falha na requisição:", error);
                alert("Erro ao conectar com o servidor.");
            }
        });
    }
});