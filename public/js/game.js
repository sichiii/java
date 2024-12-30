class GomokuGame {
    constructor(boardSize = 15, gameMode = 'single') {
        this.boardSize = boardSize;
        this.gameMode = gameMode;
        this.currentPlayer = 1;
        this.board = Array(boardSize).fill().map(() => Array(boardSize).fill(0));
        this.ai = new GomokuAI(boardSize);
        this.gameActive = true;
        this.initializeBoard();
    }

    initializeBoard() {
        const boardElement = document.getElementById('game-board');
        boardElement.innerHTML = '';
        
        for (let i = 0; i < this.boardSize; i++) {
            for (let j = 0; j < this.boardSize; j++) {
                const cell = document.createElement('div');
                cell.className = 'cell';
                cell.dataset.row = i;
                cell.dataset.col = j;
                cell.addEventListener('click', (e) => this.handleMove(e));
                boardElement.appendChild(cell);
            }
        }
    }

    handleMove(event) {
        if (!this.gameActive) return;
        
        const row = parseInt(event.target.dataset.row);
        const col = parseInt(event.target.dataset.col);

        if (this.board[row][col] !== 0) return;

        if (this.gameMode === 'single' && this.currentPlayer !== 1) return;

        this.makeMove(row, col);
    }

    makeMove(row, col) {
        this.board[row][col] = this.currentPlayer;
        this.updateCell(row, col);
        
        if (this.checkWin(row, col)) {
            const winner = this.currentPlayer === 1 ? '黑子' : '白子';
            document.getElementById('game-message').textContent = `遊戲結束！${winner}獲勝！`;
            this.gameActive = false;
            setTimeout(() => {
                alert(`${winner}獲勝！`);
                this.resetGame();
            }, 100);
            return;
        }
        
        this.currentPlayer = this.currentPlayer === 1 ? 2 : 1;
        this.updateGameStatus();

        if (this.gameMode === 'single' && this.currentPlayer === 2 && this.gameActive) {
            this.makeAIMove();
        }
    }

    makeAIMove() {
        if (!this.gameActive) return;
        
        this.ai.board = this.board.map(row => [...row]);
        
        console.log('AI thinking...');
        const move = this.ai.getBestMove();
        console.log('AI decided move:', move);
        
        if (move) {
            setTimeout(() => {
                console.log('AI making move:', move);
                this.makeMove(move.x, move.y);
            }, 200);
        } else {
            console.log('AI could not find a valid move');
        }
    }

    updateCell(row, col) {
        const cell = document.querySelector(`[data-row="${row}"][data-col="${col}"]`);
        cell.classList.add(this.currentPlayer === 1 ? 'black' : 'white');
    }

    checkWin(row, col) {
        // 檢查獲勝邏輯
        const directions = [
            [1, 0], [0, 1], [1, 1], [1, -1]
        ];

        return directions.some(([dx, dy]) => {
            return this.countLine(row, col, dx, dy) >= 5;
        });
    }

    countLine(row, col, dx, dy) {
        const player = this.board[row][col];
        let count = 1;
        let x = row + dx;
        let y = col + dy;

        while (x >= 0 && x < this.boardSize && y >= 0 && y < this.boardSize && 
               this.board[x][y] === player) {
            count++;
            x += dx;
            y += dy;
        }

        x = row - dx;
        y = col - dy;

        while (x >= 0 && x < this.boardSize && y >= 0 && y < this.boardSize && 
               this.board[x][y] === player) {
            count++;
            x -= dx;
            y -= dy;
        }

        return count;
    }

    resetGame() {
        this.board = Array(this.boardSize).fill().map(() => Array(this.boardSize).fill(0));
        this.currentPlayer = 1;
        this.gameActive = true;
        this.initializeBoard();
        this.updateGameStatus();
        document.getElementById('game-message').textContent = '';
    }

    updateGameStatus() {
        const playerText = this.currentPlayer === 1 ? '黑子' : '白子';
        document.getElementById('current-player').textContent = `當前回合：${playerText}`;
    }
} 