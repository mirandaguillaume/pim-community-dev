declare enum Operator {
    MUL = "mul",
    DIV = "div",
    ADD = "add",
    SUB = "sub"
}
declare type Operation = {
    operator: string;
    value: string;
};
declare const emptyOperation: () => Operation;
export { Operator, emptyOperation };
export type { Operation };
